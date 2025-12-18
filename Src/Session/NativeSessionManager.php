<?php

declare(strict_types=1);

namespace Design\Session;

use Design\Logging\LoggerInterface;
use Design\Session\Config\SessionConfig;
use Design\Session\Config\SessionConfigurator;
use Design\Session\Exception\SessionException;
use Design\Session\Php\PhpSessionRuntime;
use Design\Session\Security\SensitiveKeyRedactor;

/**
 * Session manager using PHP native sessions.
 *
 * Responsibilities of this class:
 * - Start / stop the session
 * - Read / write values in the session
 *
 * Other responsibilities are delegated to dedicated classes:
 * - Session configuration (ini + cookies) => SessionConfigurator
 * - PHP runtime calls (session_start, headers_sent, etc.) => PhpSessionRuntime
 * - Sensitive key redaction for logs => SensitiveKeyRedactor
 */
final class NativeSessionManager implements SessionManagerInterface
{
    private LoggerInterface $logger;

    public function __construct(
        private readonly SessionConfig $config,
        LoggerInterface $logger,
        private readonly PhpSessionRuntime $runtime,
        private readonly SessionConfigurator $configurator,
        private readonly SensitiveKeyRedactor $redactor,
    ) {
        // Bind logs to avoid repeating the channel everywhere.
        $this->logger = $logger->channel('Session');
    }

    public function start(): void
    {
        if ($this->isStarted()) {
            return;
        }

        $this->ensureHeadersAreNotSent();
        $this->configurePhpSession();
        $this->startPhpSessionOrFail();

        $this->logger->info('Session started', [
            'name' => $this->runtime->name(),
            'id' => $this->safeSessionIdForLogs(),
        ]);
    }



    public function isStarted(): bool
    {
        return $this->runtime->status() === PHP_SESSION_ACTIVE;
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
            'key' => $this->redactor->redact($key),
        ]);
    }

    public function remove(string $key): void
    {
        $this->ensureStarted();

        unset($_SESSION[$key]);

        $this->logger->debug('Session key removed', [
            'key' => $this->redactor->redact($key),
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

    public function regenerate(bool $deleteOldSession = true): void
    {
        $this->ensureStarted();

        $oldId = $this->safeSessionIdForLogs();

        $this->regenerateIdOrFail($deleteOldSession, $oldId);

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

        $this->clearSessionData();
        $this->destroyPhpSessionOrFail($id);
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

    private function deleteSessionCookie(): void
    {
        // If headers are already sent, we can't reliably delete the cookie.
        if ($this->runtime->headersSent()) {
            return;
        }

        setcookie(
            name: $this->runtime->name(),
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

    private function safeSessionIdForLogs(): string
    {
        $id = $this->runtime->id();

        // Avoid dumping full session id in logs; keep a short fingerprint.
        return $id === '' ? '' : substr(hash('sha256', $id), 0, 12);
    }

    private function ensureHeadersAreNotSent(): void
    {
        if ($this->runtime->headersSent()) {
            $this->logger->error('Cannot start session: headers already sent');
            throw new SessionException('Cannot start session because headers have already been sent.');
        }
    }

    private function configurePhpSession(): void
    {
        // ini + cookie params
        $this->configurator->apply($this->config, $this->runtime);

        // session name
        if ($this->config->name !== '') {
            $this->runtime->setName($this->config->name);
        }
    }

    private function regenerateIdOrFail(bool $deleteOldSession, string $oldId): void
    {
        if ($this->runtime->regenerateId($deleteOldSession) !== true) {
            $this->logger->error('Failed to regenerate session id', [
                'oldId' => $oldId,
            ]);
            throw new SessionException('Unable to regenerate session id.');
        }
    }

    private function clearSessionData(): void
    {
        $_SESSION = [];
    }

    private function destroyPhpSessionOrFail(string $id): void
    {
        if ($this->runtime->destroy() !== true) {
            $this->logger->error('Failed to destroy session', ['id' => $id]);
            throw new SessionException('Unable to destroy session.');
        }
    }

    private function startPhpSessionOrFail(): void
    {
        if ($this->runtime->start() !== true) {
            $this->logger->error('Failed to start session');
            throw new SessionException('Unable to start session.');
        }
    }
}
