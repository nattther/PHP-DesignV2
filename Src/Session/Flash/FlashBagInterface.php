<?php

declare(strict_types=1);

namespace Design\Session\Flash;

/**
 * Stores short-lived messages between two requests (POST -> redirect -> GET).
 *
 * Typical usage:
 * - set('success', 'Saved!')
 * - consume('success') on next page to display it once
 */
interface FlashBagInterface
{
    public function set(string $key, mixed $value): void;

    public function consume(string $key, mixed $default = null): mixed;

    public function clear(): void;
}
