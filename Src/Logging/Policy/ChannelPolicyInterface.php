<?php

declare(strict_types=1);

namespace Design\Logging\Policy;

use Design\Logging\ValueObject\Channel;
use Design\Logging\ValueObject\LogLevel;

/**
 * Decides which channel should be used for a log entry.
 *
 * Why we need this:
 * - Sometimes the caller does not provide a channel.
 * - We still want consistent routing (example: errors go to "Errors" by default).
 *
 * How it works:
 * - If a channel is explicitly requested, it is usually kept.
 * - Otherwise, the implementation chooses a default based on the log level.
 */
interface ChannelPolicyInterface
{
    /**
     * Returns the final channel to use for this log entry.
     *
     * @param Channel|null $requested Channel requested by the caller (null if none)
     * @param LogLevel     $level     Log level (Info, Error, Critical, etc.)
     */
    public function choose(?Channel $requested, LogLevel $level): Channel;
}
