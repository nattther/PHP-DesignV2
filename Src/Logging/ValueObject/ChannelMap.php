<?php

declare(strict_types=1);

namespace Design\Logging\ValueObject;

use Design\Logging\Exception\LogWriteException;

/**
 * Stores the association between a channel name and the log file name to use.
 *
 * Example:
 * - "Auth"     => "Auth.log"
 * - "Database" => "Database.log"
 *
 * Why this exists:
 * - Avoid passing raw arrays everywhere (arrays are easy to misuse / misspell).
 * - Centralize validation of file names (security + consistency).
 * - Make it very clear where the "channel -> file" configuration lives.
 *
 * Important:
 * - This object only stores file NAMES (ex: "Auth.log"), not full paths.
 * - The full path is built later by FilePathResolver using LogsDirectory.
 */
final readonly class ChannelMap
{
    /**
     * Internal map: sanitized channel name => validated file name
     *
     * @var array<string, string>
     */
    private array $map;

    /**
     * Private constructor: the only way to create this object is through
     * the named constructors (defaults() / fromArray()) so validation always runs.
     *
     * @param array<string, string> $map Sanitized channel name => validated file name
     */
    private function __construct(array $map)
    {
        $this->map = $map;
    }

    /**
     * Returns the default mapping used by the project.
     *
     * This is a convenient "ready to use" configuration.
     * You can edit the list here if you add/remove channels.
     */
    public static function defaults(): self
    {
        return self::fromArray([
            'App' => 'App.log',
            'Errors' => 'Errors.log',
            'Auth' => 'Auth.log',
            'Database' => 'Database.log',
            'Audit' => 'Audit.log',
            'Http' => 'Http.log',
            'Session' => 'Session.log',
        ]);
    }

    /**
     * Builds a ChannelMap from a raw array.
     *
     * What this method does:
     * - Cleans the channel names (so " Auth " becomes "Auth")
     * - Validates file names (must look like a safe file name and end with ".log")
     *
     * @param array<string, string> $channelToFileName Channel => file name
     * @throws LogWriteException If a file name is empty or invalid
     */
    public static function fromArray(array $channelToFileName): self
    {
        $normalized = [];

        foreach ($channelToFileName as $channel => $fileName) {
            $safeChannel = Channel::fromString((string) $channel)->name;
            $safeFileName = self::assertValidFileName((string) $fileName, $safeChannel);
            $normalized[$safeChannel] = $safeFileName;
        }

        return new self($normalized);
    }

    /**
     * Returns the file name to use for the given channel.
     *
     * If the channel is not mapped, we fallback to "<ChannelName>.log".
     * Example: channel "Payments" => "Payments.log"
     */
    public function fileNameFor(Channel $channel): string
    {
        return $this->map[$channel->name] ?? ($channel->name . '.log');
    }

    /**
     * Validates a file name for log writing.
     *
     * Rules:
     * - Must not be empty
     * - Must be a "simple" file name (no folders, no traversal)
     * - Must end with ".log"
     *
     * Accepted examples:
     * - "Auth.log"
     * - "my-service.log"
     * - "audit_2025.log"
     */
    private static function assertValidFileName(string $fileName, string $channel): string
    {
        $trimmed = trim($fileName);

        if ($trimmed === '') {
            throw new LogWriteException("Empty log file name for channel '{$channel}'.");
        }

        if (!preg_match('/^[A-Za-z0-9._-]+\.log$/', $trimmed)) {
            throw new LogWriteException("Invalid log file name '{$trimmed}' for channel '{$channel}'.");
        }

        return $trimmed;
    }
}
