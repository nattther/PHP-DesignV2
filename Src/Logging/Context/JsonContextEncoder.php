<?php

declare(strict_types=1);

namespace Design\Logging\Context;

use Design\Logging\Exception\LogWriteException;

/**
 * Encodes the context array as JSON.
 *
 * Output example:
 *   {"userId":123,"ip":"127.0.0.1"}
 *
 * Notes:
 * - If the context is empty, we return an empty string to keep log lines short.
 * - JSON_UNESCAPED_UNICODE keeps accents readable (no "\u00e9" everywhere).
 * - JSON_THROW_ON_ERROR ensures we don't silently write broken/partial JSON.
 */
final class JsonContextEncoder implements ContextEncoderInterface
{
    /**
     * @param array<string, mixed> $context
     */
    public function encode(array $context): string
    {
        // No extra data => nothing to append to the log line.
        if ($context === []) {
            return '';
        }

        try {
            return json_encode($context, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
        } catch (\JsonException $e) {
            throw new LogWriteException('Unable to encode log context as JSON.', 0, $e);
        }
    }
}
