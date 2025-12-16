<?php

declare(strict_types=1);

namespace Design\Logging\LineFormatter;

use Design\Logging\Context\ContextEncoderInterface;
use Design\Logging\ValueObject\LogEntry;

/**
 * Formats a LogEntry as a simple, human-readable single line.
 *
 * Output example:
 *   2025-12-16T10:15:30+01:00 | INFO     | Auth     | User logged in | {"id":123}
 *
 * Formatting choices:
 * - Columns are separated with " | " to stay readable in plain text.
 * - Level and channel are padded to align vertically when you scan the file.
 * - Context is appended only when not empty.
 */
final readonly class SimpleLogLineFormatter implements LogLineFormatterInterface
{
    /**
     * @param ContextEncoderInterface $contextEncoder Encodes the context array (usually JSON)
     */
    public function __construct(private ContextEncoderInterface $contextEncoder) {}

    /**
     * Builds the final text line to be written in the log file.
     *
     * Note:
     * - We add PHP_EOL at the end so each entry is on its own line.
     */
    public function format(LogEntry $entry): string
    {

        $contextText = $this->contextEncoder->encode($entry->context);
        $levelColumn = str_pad($entry->level->value, 8, ' ', STR_PAD_RIGHT);
        $channelColumn = str_pad($entry->channel->name, 8, ' ', STR_PAD_RIGHT);
        $contextSuffix = $contextText !== '' ? ' | ' . $contextText : '';
        return sprintf(
            '%s | %s | %s | %s%s',
            $entry->timestampIso8601,
            $levelColumn,
            $channelColumn,
            $entry->message,
            $contextSuffix
        ) . PHP_EOL;
    }
}
