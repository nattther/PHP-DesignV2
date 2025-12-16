<?php

declare(strict_types=1);

namespace Design\Logging\Path;

use Design\Logging\ValueObject\Channel;
use Design\Logging\ValueObject\ChannelMap;

/**
 * Builds the full path of the log file for a given channel.
 *
 * Example:
 * - LogsDirectory = "/var/www/project/Logs"
 * - Channel = "Auth"
 * - ChannelMap says "Auth" => "Auth.log"
 * Result:
 * - "/var/www/project/Logs/Auth.log"
 *
 * This class does NOT write anything.
 * It only decides "which file should receive this log entry?".
 */
final readonly class FilePathResolver
{
    public function __construct(
        private LogsDirectory $logsDirectory,
        private ChannelMap $channelMap,
    ) {}

    /**
     * Returns the absolute file path where logs of this channel must be appended.
     */
    public function resolve(Channel $channel): string
    {
        return $this->logsDirectory->path
            . DIRECTORY_SEPARATOR
            . $this->channelMap->fileNameFor($channel);
    }
}
