<?php

declare(strict_types=1);

namespace Design\Logging\Channel;

use Design\Logging\LoggerInterface;
use Design\Logging\ValueObject\Channel;
use Design\Logging\ValueObject\LogLevel;

/**
 * Logger wrapper that permanently applies a channel.
 *
 * Why this exists:
 * - So developers can write:
 *     $logger->channel('Auth')->info('User logged in');
 *   instead of repeating the channel on every call.
 *
 * How it works:
 * - This class wraps another logger ("inner").
 * - It forces a specific channel for all log calls.
 * - If the caller tries to pass a different channel, it is ignored on purpose
 *   because this logger is already "locked" to its channel.
 */
final readonly class ChannelBoundLogger implements LoggerInterface
{
    /**
     * @param LoggerInterface $inner   The real logger that will write the log entry
     * @param Channel         $channel The fixed channel to use for all calls
     */
    public function __construct(
        private LoggerInterface $inner,
        private Channel $channel,
    ) {}

    /**
     * Writes a log entry using the fixed channel of this instance.
     *
     * Even if a channel is provided by the caller, we ignore it here on purpose:
     * this logger is already bound to $this->channel.
     *
     * @param array<string, mixed> $context
     */
    public function log(LogLevel $level, string $message, array $context = [], Channel|string|null $channel = null): void
    {
        $this->inner->log($level, $message, $context, $this->channel);
    }

    /** @param array<string, mixed> $context */
    public function debug(string $message, array $context = []): void
    {
        $this->log(LogLevel::Debug, $message, $context);
    }

    /** @param array<string, mixed> $context */
    public function info(string $message, array $context = []): void
    {
        $this->log(LogLevel::Info, $message, $context);
    }

    /** @param array<string, mixed> $context */
    public function warning(string $message, array $context = []): void
    {
        $this->log(LogLevel::Warning, $message, $context);
    }

    /** @param array<string, mixed> $context */
    public function error(string $message, array $context = []): void
    {
        $this->log(LogLevel::Error, $message, $context);
    }

    /** @param array<string, mixed> $context */
    public function critical(string $message, array $context = []): void
    {
        $this->log(LogLevel::Critical, $message, $context);
    }

    /**
     * Allows creating a new bound logger with another channel.
     *
     * Example:
     *   $authLogger = $logger->channel('Auth');
     *   $dbLogger   = $authLogger->channel('Database'); // rebinds to Database
     */
    public function channel(Channel|string $channel): LoggerInterface
    {
        return $this->inner->channel($channel);
    }
}
