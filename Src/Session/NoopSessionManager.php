<?php

declare(strict_types=1);

namespace Design\Session;

/**
 * Session manager that does nothing.
 *
 * Useful for CLI / cron jobs where PHP sessions are not relevant.
 * It keeps the Kernel API consistent: $kernel->session() always exists.
 */
final class NoopSessionManager implements SessionManagerInterface
{
    public function start(): void {}
    public function isStarted(): bool { return false; }
    public function has(string $key): bool { return false; }
    public function get(string $key, mixed $default = null): mixed { return $default; }
    public function set(string $key, mixed $value): void {}
    public function remove(string $key): void {}
    public function all(): array { return []; }
    public function clear(): void {}
    public function regenerate(bool $deleteOldSession = true): void {}
    public function destroy(): void {}
}
