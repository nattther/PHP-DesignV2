<?php

declare(strict_types=1);

namespace Design\Logging\LineFormatter;

use Design\Logging\ValueObject\LogEntry;

/**
 * Turns a LogEntry object into the final text line written in the log file.
 *
 * Example output (depending on implementation):
 *   2025-12-16T10:15:30+01:00 | INFO     | Auth     | User logged in | {"id":123}
 *
 * Why this exists:
 * - The logger should not care about the exact text format.
 * - We can change the format later (JSON lines, different separators, etc.)
 *   without touching the writing logic.
 */
interface LogLineFormatterInterface
{
    /**
     * Builds the final log line (including the trailing newline).
     *
     * Important:
     * - Implementations should usually add PHP_EOL at the end, because the writer
     *   typically appends exactly what it receives.
     */
    public function format(LogEntry $entry): string;
}
