<?php

declare(strict_types=1);

namespace Design\Session;

interface SessionManagerInterface
{
    public function start(): void;

    public function isStarted(): bool;

    public function has(string $key): bool;

    public function get(string $key, mixed $default = null): mixed;

    public function set(string $key, mixed $value): void;

    public function remove(string $key): void;

    /**
     * @return array<string, mixed>
     */
    public function all(): array;

    public function clear(): void;

    /**
     * Regenerates the session id (useful after login).
     */
    public function regenerate(bool $deleteOldSession = true): void;

    /**
     * Destroys the session + clears cookie.
     */
    public function destroy(): void;
}
