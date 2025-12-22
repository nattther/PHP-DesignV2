<?php

declare(strict_types=1);

namespace Design\Tests\AuthTest\Support;

use Design\Session\SessionManagerInterface;

final class InMemorySessionManager implements SessionManagerInterface
{
    private bool $started = true;

    /** @var array<string, mixed> */
    private array $data = [];

    public function start(): void { $this->started = true; }
    public function isStarted(): bool { return $this->started; }

    public function set(string $key, mixed $value): void { $this->data[$key] = $value; }
    public function get(string $key, mixed $default = null): mixed { return $this->data[$key] ?? $default; }
    public function has(string $key): bool { return array_key_exists($key, $this->data); }
    public function remove(string $key): void { unset($this->data[$key]); }

    /** @return array<string, mixed> */
    public function all(): array { return $this->data; }

    public function clear(): void { $this->data = []; }
    public function regenerate(bool $deleteOldSession = true): void {}
    public function destroy(): void { $this->data = []; $this->started = false; }
}
