<?php

declare(strict_types=1);

namespace Design\Logging;

use Design\Logging\ValueObject\Channel;
use Design\Logging\ValueObject\LogLevel;

/**
 * Common contract for logging in the application.
 *
 * Anything that "logs" should implement this interface, so the rest of the code
 * does not depend on a specific implementation (file logger, test logger, etc.).
 *
 * Typical usage:
 * - $logger->info('Something happened');
 * - $logger->error('Something failed', ['id' => 123]);
 * - $logger->channel('Auth')->info('User logged in');
 */
interface LoggerInterface
{
    /**
     * Writes a log entry.
     *
     * Most code will call info()/warning()/error() instead of calling log() directly.
     *
     * Channel behavior:
     * - If $channel is null, the logger will choose a default channel automatically.
     * - You can also use $logger->channel('Auth') to avoid repeating the channel.
     *
     * @param LogLevel $level   Severity (Info, Warning, Error, ...)
     * @param string   $message Main human-readable message
     * @param array<string, mixed> $context Extra details (ids, payload, timing...)
     * @param Channel|string|null  $channel Optional channel (can be a Channel object or a string)
     */
    public function log(
        LogLevel $level,
        string $message,
        array $context = [],
        Channel|string|null $channel = null
    ): void;

    /** @param array<string, mixed> $context */
    public function debug(string $message, array $context = []): void;

    /** @param array<string, mixed> $context */
    public function info(string $message, array $context = []): void;

    /** @param array<string, mixed> $context */
    public function warning(string $message, array $context = []): void;

    /** @param array<string, mixed> $context */
    public function error(string $message, array $context = []): void;

    /** @param array<string, mixed> $context */
    public function critical(string $message, array $context = []): void;

    /**
     * Returns a logger that will always use the given channel.
     *
     * Example:
     *   $authLogger = $logger->channel('Auth');
     *   $authLogger->info('User logged in');
     */
    public function channel(Channel|string $channel): LoggerInterface;
}
