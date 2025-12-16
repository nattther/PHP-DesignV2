<?php

declare(strict_types=1);

namespace Design\Logging\ValueObject;

/**
 * Represents ONE log message to be written.
 *
 * Think of it as a "package" that contains everything needed to write a log line:
 * - When it happened (timestamp)
 * - How important it is (level)
 * - Where it should go (channel)
 * - The human message (message)
 * - Extra information (context)
 *
 * This object is intentionally simple and immutable:
 * once created, it should not change.
 */
final readonly class LogEntry
{
    /**
     * @param string $timestampIso8601 Time of the log (ISO 8601 format)
     * @param LogLevel $level          Severity (INFO, WARNING, ERROR...)
     * @param Channel $channel         Category / destination (App, Auth, Database...)
     * @param string $message          Main message to display in the log
     * @param array<string, mixed> $context Extra data (ids, payload, timing...)
     */
    public function __construct(
        public string $timestampIso8601,
        public LogLevel $level,
        public Channel $channel,
        public string $message,
        public array $context = [],
    ) {}
}
