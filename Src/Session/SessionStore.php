<?php

declare(strict_types=1);

final class SessionStore
{
    public function __construct(
        private readonly string $namespace = 'app'
    ) {
        if ($this->namespace === '' || str_starts_with($this->namespace, '__')) {
            throw new InvalidArgumentException('Invalid session namespace.');
        }
    }

    public function set(string $key, mixed $value): void
    {
        $bag = &$this->bag();
        $bag[$this->assertKey($key)] = $value;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $bag = &$this->bag();
        $key = $this->assertKey($key);

        return array_key_exists($key, $bag) ? $bag[$key] : $default;
    }

    public function exists(string $key): bool
    {
        $bag = &$this->bag();
        return array_key_exists($this->assertKey($key), $bag);
    }

    public function remove(string $key): void
    {
        $bag = &$this->bag();
        unset($bag[$this->assertKey($key)]);
    }

    /**
     * Get all variables in this namespace.
     *
     * @return array<string, mixed>
     */
    public function all(): array
    {
        $bag = &$this->bag();
        return $bag;
    }

    public function clear(): void
    {
        $bag = &$this->bag();
        $bag = [];
    }

    // ------------------ Internals ------------------

    private function &bag(): array
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            throw new RuntimeException('Session is not started. SessionManager::start() must be called first.');
        }

        if (!isset($_SESSION[$this->namespace]) || !is_array($_SESSION[$this->namespace])) {
            $_SESSION[$this->namespace] = [];
        }

        /** @var array $bag */
        $bag = &$_SESSION[$this->namespace];
        return $bag;
    }

    private function assertKey(string $key): string
    {
        $key = trim($key);
        if ($key === '' || str_contains($key, "\0")) {
            throw new InvalidArgumentException('Invalid session key.');
        }
        return $key;
    }
}
