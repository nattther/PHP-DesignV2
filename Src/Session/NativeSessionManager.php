<?php

declare(strict_types=1);

namespace Design\Session;

use Design\Logging\LoggerInterface;

/**
 * Session manager based on PHP native sessions.
 *
 * Logs:
 * - Uses the "Session" channel.
 * - Logs keys, never values (to avoid leaking sensitive data).
 */
final class NativeSessionManager implements SessionManagerInterface
{
    private const FLASH_BAG = '__flash__';

    /** @var array<string, true> */
    private array $sensitiveKeys;

    private LoggerInterface $logger;

    /**
     * @param array<int, string> $sensitiveKeys Keys for which we avoid logging the key name (optional).
     */
    public function __construct(
        private readonly SessionConfig $config,
        LoggerInterface $logger,
        array $sensitiveKeys = ['password', 'token', 'csrf', 'auth', 'jwt']
    ) {
        // Bind logs to Session channel to avoid repeating it everywhere.
        $this->logger = $logger->channel('Session');

        $this->sensitiveKeys = [];
        foreach ($sensitiveKeys as $k) {
            $this->sensitiveKeys[(string) $k] = true;
        }
    }

    public function start(): void
    {
        if ($this->isStarted()) {
            return;
        }

        // Apply config before session_start()
        $this->applyPhpIniConfig();
        $this->applyCookieParams();

        // Name should be set before start
        if ($this->config->name !== '') {
            session_name($this->config->name);
        }

        if (!headers_sent()) {
            $ok = @session_start();
            if ($ok !== true) {
                $this->logger->error('Failed to start session');
                throw new SessionException('Unable to start session.');
            }

            $this->logger->info('Session started', [
                'name' => session_name(),
                'id' => $this->safeSessionIdForLogs(),
            ]);
            return;
        }

        // If headers are already sent, cookies cannot be set reliably.
        $this->logger->error('Cannot start session: headers already sent');
        throw new SessionException('Cannot start session because headers have already been sent.');
    }

    public function isStarted(): bool
    {
        return session_status() === PHP_SESSION_ACTIVE;
    }

    public function has(string $key): bool
    {
        $this->ensureStarted();

        return array_key_exists($key, $_SESSION);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $this->ensureStarted();

        return $_SESSION[$key] ?? $default;
    }

    public function set(string $key, mixed $value): void
    {
        $this->ensureStarted();

        $_SESSION[$key] = $value;

        $this->logger->debug('Session key set', [
            'key' => $this->safeKeyForLogs($key),
        ]);
    }

    public function remove(string $key): void
    {
        $this->ensureStarted();

        unset($_SESSION[$key]);

        $this->logger->debug('Session key removed', [
            'key' => $this->safeKeyForLogs($key),
        ]);
    }

    public function all(): array
    {
        $this->ensureStarted();

        return $_SESSION;
    }

    public function clear(): void
    {
        $this->ensureStarted();

        $_SESSION = [];

        $this->logger->info('Session cleared', [
            'id' => $this->safeSessionIdForLogs(),
        ]);
    }

    public function flash(string $key, mixed $value): void
    {
        $this->ensureStarted();

        if (!isset($_SESSION[self::FLASH_BAG]) || !is_array($_SESSION[self::FLASH_BAG])) {
            $_SESSION[self::FLASH_BAG] = [];
        }

        $_SESSION[self::FLASH_BAG][$key] = $value;

        $this->logger->debug('Flash set', [
            'key' => $this->safeKeyForLogs($key),
        ]);
    }

    public function consumeFlash(string $key, mixed $default = null): mixed
    {
        $this->ensureStarted();

        $bag = $_SESSION[self::FLASH_BAG] ?? null;
        if (!is_array($bag) || !array_key_exists($key, $bag)) {
            return $default;
        }

        $value = $bag[$key];
        unset($_SESSION[self::FLASH_BAG][$key]);

        // If bag becomes empty, clean it.
        if ($_SESSION[self::FLASH_BAG] === []) {
            unset($_SESSION[self::FLASH_BAG]);
        }

        $this->logger->debug('Flash consumed', [
            'key' => $this->safeKeyForLogs($key),
        ]);

        return $value;
    }

    public function regenerate(bool $deleteOldSession = true): void
    {
        $this->ensureStarted();

        $oldId = $this->safeSessionIdForLogs();
        $ok = @session_regenerate_id($deleteOldSession);

        if ($ok !== true) {
            $this->logger->error('Failed to regenerate session id', [
                'oldId' => $oldId,
            ]);
            throw new SessionException('Unable to regenerate session id.');
        }

        $this->logger->info('Session id regenerated', [
            'oldId' => $oldId,
            'newId' => $this->safeSessionIdForLogs(),
        ]);
    }

    public function destroy(): void
    {
        if (!$this->isStarted()) {
            return;
        }

        $id = $this->safeSessionIdForLogs();

        // Clear data first
        $_SESSION = [];

        // Destroy session storage
        $ok = @session_destroy();
        if ($ok !== true) {
            $this->logger->error('Failed to destroy session', ['id' => $id]);
            throw new SessionException('Unable to destroy session.');
        }

        // Remove cookie (best-effort)
        $this->deleteSessionCookie();

        $this->logger->info('Session destroyed', ['id' => $id]);
    }

    private function ensureStarted(): void
    {
        if (!$this->isStarted()) {
            $this->logger->error('Session not started');
            throw new SessionException('Session is not started. Call start() first.');
        }
    }

    private function applyPhpIniConfig(): void
    {
        if ($this->config->useStrictMode) {
            ini_set('session.use_strict_mode', '1');
        }

        // Recommended hardening (optional)
        ini_set('session.use_only_cookies', '1');
        ini_set('session.use_trans_sid', '0');
    }

    private function applyCookieParams(): void
    {
        // Must be done before session_start()
        session_set_cookie_params([
            'lifetime' => $this->config->cookieLifetime,
            'path' => $this->config->cookiePath,
            'domain' => $this->config->cookieDomain,
            'secure' => $this->config->cookieSecure,
            'httponly' => $this->config->cookieHttpOnly,
            'samesite' => $this->config->cookieSameSite,
        ]);
    }

    private function deleteSessionCookie(): void
    {
        if (headers_sent()) {
            return;
        }

        setcookie(
            name: session_name(),
            value: '',
            expires_or_options: [
                'expires' => time() - 3600,
                'path' => $this->config->cookiePath,
                'domain' => $this->config->cookieDomain,
                'secure' => $this->config->cookieSecure,
                'httponly' => $this->config->cookieHttpOnly,
                'samesite' => $this->config->cookieSameSite,
            ]
        );
    }

    private function safeKeyForLogs(string $key): string
    {
        // If the key is sensitive, don’t expose it. (Even the key name can reveal intent.)
        if (isset($this->sensitiveKeys[$key])) {
            return '[REDACTED]';
        }

        return $key;
    }

    private function safeSessionIdForLogs(): string
    {
        // Avoid dumping full session id in logs; keep a short fingerprint.
        $id = session_id();
        if ($id === '') {
            return '';
        }

        return substr(hash('sha256', $id), 0, 12);
    }
}
