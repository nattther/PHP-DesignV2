<?php

declare(strict_types=1);

namespace Design\Logging\Clock;

/**
 * Provides the current time for the logging system.
 *
 * Why this exists:
 * - We want the logger to ask "what time is it?" without depending directly on PHP's global time functions.
 * - This makes the code easier to control in tests (a test clock can return a fixed date).
 *
 * Expected format:
 * - The returned string must be a valid ISO 8601 timestamp (e.g. 2025-12-16T10:15:30+01:00).
 */
interface ClockInterface
{
    /**
     * Returns the current date/time as an ISO 8601 string.
     *
     * @return string ISO 8601 timestamp (example: 2025-12-16T10:15:30+01:00)
     */
    public function nowIso8601(): string;
}
