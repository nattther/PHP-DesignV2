<?php

declare(strict_types=1);

final class SessionManager
{
    private const META_KEY = '__sm_meta';

    /** @var array{idle_timeout:int, regen_interval:int, cookie_secure:bool|null, cookie_httponly:bool, cookie_samesite:string, cookie_path:string, cookie_domain:string} */
    private array $config;

    public function __construct(array $config = [])
    {
        $this->config = array_merge([
            'idle_timeout'    => 20 * 60, // 20 min
            'regen_interval'  => 10 * 60, // 10 min
            'cookie_secure'   => null,    // null => auto HTTPS
            'cookie_httponly' => true,
            'cookie_samesite' => 'Lax',
            'cookie_path'     => '/',
            'cookie_domain'   => '',
        ], $config);
    }

    /**
     * Start session safely (call early, before any output).
     */
    public function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            $this->protect();
            return;
        }

        if (headers_sent($file, $line)) {
            throw new RuntimeException("Cannot start session: headers already sent in {$file}:{$line}.");
        }

        $this->applyIniHardening();
        $this->applyCookieParams();

        if (!session_start()) {
            throw new RuntimeException('Failed to start session.');
        }

        // First start => regenerate once (fixation protection)
        $meta = &$this->meta();
        if (!isset($meta['initiated_at'])) {
            $this->regenerateId();
            $meta['initiated_at'] = time();
        }

        $this->protect();
    }

    public function isStarted(): bool
    {
        return session_status() === PHP_SESSION_ACTIVE;
    }

    /**
     * Regenerate session ID (call after login / privilege change).
     */
    public function regenerateId(): void
    {
        $this->assertStarted();

        if (!session_regenerate_id(true)) {
            throw new RuntimeException('Failed to regenerate session id.');
        }

        $this->meta()['last_regen_at'] = time();
    }

    /**
     * Destroy session and delete cookie.
     */
    public function destroy(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return;
        }

        $_SESSION = [];

        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            [
                'expires'  => time() - 42000,
                'path'     => $params['path'] ?? '/',
                'domain'   => $params['domain'] ?? '',
                'secure'   => (bool)($params['secure'] ?? false),
                'httponly' => (bool)($params['httponly'] ?? true),
                'samesite' => $params['samesite'] ?? 'Lax',
            ]
        );

        session_destroy();
    }

    // ------------------ Internals ------------------

    private function protect(): void
    {
        $this->enforceIdleExpiration();
        $this->enforcePeriodicRegeneration();

        $this->meta()['last_activity_at'] = time();
    }

    private function enforceIdleExpiration(): void
    {
        $timeout = (int)$this->config['idle_timeout'];
        if ($timeout <= 0) {
            return;
        }

        $meta = &$this->meta();
        $last = (int)($meta['last_activity_at'] ?? 0);

        if ($last > 0 && (time() - $last) > $timeout) {
            $this->destroy();
            $this->start(); // restart clean
        }
    }

    private function enforcePeriodicRegeneration(): void
    {
        $interval = (int)$this->config['regen_interval'];
        if ($interval <= 0) {
            return;
        }

        $meta = &$this->meta();
        $lastRegen = (int)($meta['last_regen_at'] ?? 0);

        if ($lastRegen === 0) {
            $meta['last_regen_at'] = time();
            return;
        }

        if ((time() - $lastRegen) >= $interval) {
            $this->regenerateId();
        }
    }

    private function &meta(): array
    {
        if (!isset($_SESSION[self::META_KEY]) || !is_array($_SESSION[self::META_KEY])) {
            $_SESSION[self::META_KEY] = [];
        }

        /** @var array $meta */
        $meta = &$_SESSION[self::META_KEY];
        return $meta;
    }

    private function applyIniHardening(): void
    {
        ini_set('session.use_only_cookies', '1');
        ini_set('session.use_strict_mode', '1');
        ini_set('session.cookie_httponly', $this->config['cookie_httponly'] ? '1' : '0');
    }

    private function applyCookieParams(): void
    {
        $secure = $this->config['cookie_secure'];
        if ($secure === null) {
            $secure = $this->isHttpsRequest();
        }

        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => $this->config['cookie_path'],
            'domain'   => $this->config['cookie_domain'],
            'secure'   => (bool)$secure,
            'httponly' => (bool)$this->config['cookie_httponly'],
            'samesite' => (string)$this->config['cookie_samesite'],
        ]);
    }

    private function isHttpsRequest(): bool
    {
        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            return true;
        }
        if (!empty($_SERVER['SERVER_PORT']) && (string)$_SERVER['SERVER_PORT'] === '443') {
            return true;
        }
        if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower((string)$_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https') {
            return true;
        }
        return false;
    }

    private function assertStarted(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            throw new RuntimeException('Session is not started. Call start() first.');
        }
    }
}
