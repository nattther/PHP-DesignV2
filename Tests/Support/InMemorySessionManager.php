<?php
declare(strict_types=1);

namespace Design\Tests\Support;

use Design\Session\Exception\SessionException;
use Design\Session\SessionManagerInterface;

final class InMemorySessionManager implements SessionManagerInterface
{
    /** @var array<string, mixed> */
    private array $data = [];

    public function __construct(private bool $started = true) {}

    public function start(): void { $this->started = true; }
    public function isStarted(): bool { return $this->started; }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->data);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        if (!$this->started) {
            throw new SessionException('Session is not started. Call start() first.');
        }
        return $this->data[$key] ?? $default;
    }

    public function set(string $key, mixed $value): void
    {
        if (!$this->started) {
            throw new SessionException('Session is not started. Call start() first.');
        }
        $this->data[$key] = $value;
    }

    public function remove(string $key): void
    {
        if (!$this->started) {
            throw new SessionException('Session is not started. Call start() first.');
        }
        unset($this->data[$key]);
    }

    public function all(): array { return $this->data; }
    public function clear(): void { $this->data = []; }
    public function regenerate(bool $deleteOldSession = true): void {}
    public function destroy(): void { $this->started = false; $this->data = []; }
}
