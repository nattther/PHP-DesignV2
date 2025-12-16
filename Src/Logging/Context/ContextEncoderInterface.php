<?php

declare(strict_types=1);

namespace Design\Logging\Context;

/**
 * Converts a log "context" array into a string that can be written in the log line.
 *
 * What is "context"?
 * - Extra data attached to a log message (ids, payloads, timings, etc.).
 *
 * Why we use an interface:
 * - So we can change how context is rendered (JSON, key=value, pretty format...)
 *   without changing the logger itself.
 */
interface ContextEncoderInterface
{
    /**
     * Turns the context array into a string.
     *
     * Rules:
     * - If the context is empty, implementations usually return an empty string.
     * - The returned string should be safe to append to a log line.
     *
     * @param array<string, mixed> $context Extra data to attach to the log message
     * @return string Encoded representation of the context (often JSON)
     */
    public function encode(array $context): string;
}
