<?php

declare(strict_types=1);

namespace Design\Logging\Clock;

/**
 * Default clock implementation used in production.
 *
 * It returns the current time from the server (system clock).
 * The logger uses this to timestamp each log entry.
 *
 * Note:
 * - If you want predictable timestamps in tests, create another implementation
 *   of ClockInterface that returns a fixed date/time.
 */
final class SystemClock implements ClockInterface
{
    /**
     * Returns the current server time as an ISO 8601 string.
     *
     * Example output: "2025-12-16T10:15:30+01:00"
     */
    public function nowIso8601(): string
    {
        return (new \DateTimeImmutable('now'))->format(\DateTimeInterface::ATOM);
    }
}
