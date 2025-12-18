<?php

declare(strict_types=1);

namespace Design\Session\Flash;

use Design\Session\SessionManagerInterface;

/**
 * Stores "flash" messages inside the session.
 *
 * A flash message is meant to be displayed ONLY once:
 * - You set it (often during a POST handler)
 * - You redirect to another page
 * - On the next page, you read it and it disappears automatically
 */
final class SessionFlashBag implements FlashBagInterface
{
    public function __construct(
        private readonly SessionManagerInterface $session,
        private readonly string $bagKey = '__flash__'
    ) {}

    public function set(string $key, mixed $value): void
    {
        $bag = $this->readBag();
        $bag[$key] = $value;

        $this->session->set($this->bagKey, $bag);
    }

    public function consume(string $key, mixed $default = null): mixed
    {
        $bag = $this->readBag();

        if (!array_key_exists($key, $bag)) {
            return $default;
        }

        $value = $bag[$key];
        unset($bag[$key]);

        if ($bag === []) {
            $this->session->remove($this->bagKey);
        } else {
            $this->session->set($this->bagKey, $bag);
        }

        return $value;
    }

    public function clear(): void
    {
        $this->session->remove($this->bagKey);
    }

    /**
     * @return array<string, mixed>
     */
    private function readBag(): array
    {
        $value = $this->session->get($this->bagKey, []);

        return is_array($value) ? $value : [];
    }
}
