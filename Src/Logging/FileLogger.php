<?php

declare(strict_types=1);

namespace Design\Logging;

use Design\Logging\Channel\ChannelBoundLogger;
use Design\Logging\Clock\ClockInterface;
use Design\Logging\LineFormatter\LogLineFormatterInterface;
use Design\Logging\Path\FilePathResolver;
use Design\Logging\Policy\ChannelPolicyInterface;
use Design\Logging\ValueObject\Channel;
use Design\Logging\ValueObject\LogEntry;
use Design\Logging\ValueObject\LogLevel;
use Design\Logging\Writer\FileWriterInterface;

/**
 * Logger implementation that writes logs into local files.
 *
 * What this class does, step by step:
 * 1) Decide which channel to use (App / Errors / Auth / etc.)
 * 2) Build a LogEntry object (timestamp + level + channel + message + context)
 * 3) Convert the LogEntry to a text line (formatter)
 * 4) Resolve the target file path (ex: ".../Logs/Auth.log")
 * 5) Append the line to the file (writer)
 *
 * What this class does NOT do:
 * - It does not decide the text format itself (handled by a formatter)
 * - It does not decide the file mapping itself (handled by the path resolver + channel map)
 * - It does not read environment variables or configuration directly (handled by the factory)
 */
final readonly class FileLogger implements LoggerInterface
{
    /**
     * @param ClockInterface           $clock         Provides the current time for timestamps
     * @param FilePathResolver         $pathResolver  Builds the full path of the target log file
     * @param LogLineFormatterInterface $formatter    Converts a LogEntry into a text line
     * @param FileWriterInterface      $writer        Appends the line to the chosen file
     * @param ChannelPolicyInterface   $channelPolicy Chooses a default channel when none is provided
     */
    public function __construct(
        private ClockInterface $clock,
        private FilePathResolver $pathResolver,
        private LogLineFormatterInterface $formatter,
        private FileWriterInterface $writer,
        private ChannelPolicyInterface $channelPolicy,
    ) {}

    /**
     * Returns a logger "bound" to a specific channel.
     *
     * Example:
     *   $authLogger = $logger->channel('Auth');
     *   $authLogger->info('User logged in'); // always goes to Auth.log
     *
     * This avoids repeating the channel on every call.
     */
    public function channel(Channel|string $channel): LoggerInterface
    {
        return new ChannelBoundLogger($this, Channel::from($channel));
    }


    /**
     * Writes a log entry to the appropriate file.
     *
     * @param array<string, mixed> $context Extra information attached to the message
     * @param Channel|string|null  $channel Optional channel. If null, a default channel is chosen automatically.
     */
    public function log(LogLevel $level, string $message, array $context = [], Channel|string|null $channel = null): void
    {
    
        $requestedChannel = $this->normalizeChannel($channel);

        $finalChannel = $this->channelPolicy->choose($requestedChannel, $level);

        $entry = new LogEntry(
            timestampIso8601: $this->clock->nowIso8601(),
            level: $level,
            channel: $finalChannel,
            message: $message,
            context: $context
        );

        $filePath = $this->pathResolver->resolve($entry->channel);
        $line = $this->formatter->format($entry);
        $this->writer->append($filePath, $line);
    }

    /**
     * Convenience methods for common log levels.
     *
     * They all call log() and rely on the policy to choose a default channel if needed.
     */
    public function debug(string $message, array $context = []): void
    {
        $this->log(LogLevel::Debug, $message, $context);
    }

    public function info(string $message, array $context = []): void
    {
        $this->log(LogLevel::Info, $message, $context);
    }

    public function warning(string $message, array $context = []): void
    {
        $this->log(LogLevel::Warning, $message, $context);
    }

    public function error(string $message, array $context = []): void
    {
        $this->log(LogLevel::Error, $message, $context);
    }

    public function critical(string $message, array $context = []): void
    {
        $this->log(LogLevel::Critical, $message, $context);
    }

    /**
     * Normalizes channel input:
     * - null stays null (meaning "no channel requested")
     * - a Channel is returned as-is
     * - a string is cleaned and converted into a Channel object
     */
    private function normalizeChannel(Channel|string|null $channel): ?Channel
    {
        return $channel === null ? null : Channel::from($channel);
    }
}
