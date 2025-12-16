<?php

declare(strict_types=1);

namespace Design\Logging\ValueObject;

/**
 * Represents a log channel name (ex: "App", "Auth", "Database", "Errors").
 *
 * This small object exists to avoid passing raw strings everywhere and to ensure
 * the channel is always usable as a file identifier.
 *
 * What it guarantees:
 * - No spaces
 * - No special characters that could break file names
 * - Never empty (it falls back to a default channel)
 */
final readonly class Channel
{
    /**
     * @param string $name Sanitized channel name (safe to display and use in file naming)
     */
    private function __construct(public string $name) {}

    /**
     * Creates a Channel from a raw string.
     *
     * Behavior:
     * - Trims whitespace
     * - Removes characters other than letters, numbers, "_" and "-"
     * - If the result is empty, uses the fallback channel (default: "App")
     *
     * Examples:
     * - " Auth "      => "Auth"
     * - "HTTP/API"    => "HTTPAPI"
     * - ""            => "App" (fallback)
     *
     * @param string $channel  Raw channel input
     * @param string $fallback Used when the channel is empty or becomes empty after cleaning
     */
    public static function fromString(string $channel, string $fallback = 'App'): self
    {
        $trimmed = trim($channel);

        // Empty input => fallback channel.
        if ($trimmed === '') {
            return new self($fallback);
        }

        $sanitized = preg_replace('/[^a-zA-Z0-9_-]/', '', $trimmed) ?? '';
        return new self($sanitized !== '' ? $sanitized : $fallback);
    }

    public static function from(Channel|string $channel, string $fallback = 'App'): self
    {
        return $channel instanceof self ? $channel : self::fromString($channel, $fallback);
    }
}
