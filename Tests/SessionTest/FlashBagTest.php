<?php

declare(strict_types=1);

namespace Design\Tests\SessionTest;

use Design\Session\Flash\SessionFlashBag;
use Design\Session\Exception\SessionException;
use Design\Session\SessionManagerInterface;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

final class FlashBagTest extends TestCase
{
    #[TestDox('Test de Flash — OK')]
    public function test_flash_set_consume_clear(): void
    {
        $session = new InMemorySessionManager(started: true);
        $flash = new SessionFlashBag($session);

        // set + consume => value is returned once
        $flash->set('success', 'Saved!');
        self::assertSame('Saved!', $flash->consume('success'));

        // second consume => default (because it was removed)
        self::assertSame('none', $flash->consume('success', 'none'));

        // multiple values + clear
        $flash->set('a', 1);
        $flash->set('b', 2);
        $flash->clear();

        self::assertSame('missing', $flash->consume('a', 'missing'));
        self::assertSame('missing', $flash->consume('b', 'missing'));
    }
    #[TestDox('Test Flash fails if session manager throws')]
    public function test_flash_relays_session_exception(): void
    {
        $session = new InMemorySessionManager(started: false);
        $flash = new SessionFlashBag($session);

        $this->expectException(SessionException::class);

        // This will call SessionManager::set(), which must throw
        $flash->set('x', 'y');
    }
}

/**
 * Small in-memory SessionManager for tests.
 *
 * It only needs to behave correctly for:
 * - isStarted()
 * - get()
 * - set()
 * - remove()
 *
 * Other methods exist only because it implements SessionManagerInterface.
 */
final class InMemorySessionManager implements SessionManagerInterface
{
    /** @var array<string, mixed> */
    private array $data = [];

    public function __construct(private bool $started) {}

    public function start(): void
    {
        $this->started = true;
    }

    public function isStarted(): bool
    {
        return $this->started;
    }

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


    public function all(): array
    {
        return $this->data;
    }

    public function clear(): void
    {
        $this->data = [];
    }

    public function regenerate(bool $deleteOldSession = true): void
    {
        // Not needed for these tests
    }

    public function destroy(): void
    {
        $this->started = false;
        $this->data = [];
    }
}
