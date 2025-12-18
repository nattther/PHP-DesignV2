<?php

declare(strict_types=1);

namespace Design\Tests\Support;

use Design\Logging\Clock\ClockInterface;

/**
 * Fake clock for predictable timestamps in tests.
 */
final readonly class FakeClock implements ClockInterface
{
    public function __construct(private string $iso8601) {}

    public function nowIso8601(): string
    {
        return $this->iso8601;
    }
}
