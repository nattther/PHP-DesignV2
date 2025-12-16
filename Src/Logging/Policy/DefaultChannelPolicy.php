<?php

declare(strict_types=1);

namespace Design\Logging\Policy;

use Design\Logging\ValueObject\Channel;
use Design\Logging\ValueObject\LogLevel;

/**
 * Default rule used when the caller does not specify any channel.
 *
 * Behavior:
 * - If a channel is provided by the caller, we keep it.
 * - If no channel is provided:
 *   - Error / Critical => use the "Errors" channel
 *   - Everything else  => use the "App" channel
 *
 * This keeps log routing consistent without forcing developers to think
 * about channels for every log call.
 */
final readonly class DefaultChannelPolicy implements ChannelPolicyInterface
{
    /**
     * @param Channel $defaultChannel Channel used for most messages (ex: "App")
     * @param Channel $errorChannel   Channel used for serious levels (ex: "Errors")
     */
    public function __construct(
        private Channel $defaultChannel,
        private Channel $errorChannel,
    ) {}

    /**
     * Convenience factory with the common default channels used in the project.
     *
     */
    public static function standard(): self
    {
        return new self(
            defaultChannel: Channel::fromString('App'),
            errorChannel: Channel::fromString('Errors'),
        );
    }

    /**
     * Picks the final channel for a log entry.
     *
     * @param Channel|null $requested Channel requested by the caller (null if none)
     * @param LogLevel     $level     Severity of the log (Info, Error, Critical...)
     */
    public function choose(?Channel $requested, LogLevel $level): Channel
    {

        if ($requested !== null) {
            return $requested;
        }
        return match ($level) {
            LogLevel::Error, LogLevel::Critical => $this->errorChannel,
            default => $this->defaultChannel,
        };
    }
}
