<?php

declare(strict_types=1);

namespace Design\Logging;

use Design\Logging\Clock\SystemClock;
use Design\Logging\Context\JsonContextEncoder;
use Design\Logging\LineFormatter\SimpleLogLineFormatter;
use Design\Logging\Path\FilePathResolver;
use Design\Logging\Path\LogsDirectory;
use Design\Logging\Policy\DefaultChannelPolicy;
use Design\Logging\ValueObject\ChannelMap;
use Design\Logging\Writer\LocalFileWriter;

/**
 * Creates a ready-to-use logger for the application.
 *
 * Why this factory exists:
 * - All "wiring" (choosing implementations and configuration) is done in one place.
 * - The rest of the codebase only needs a LoggerInterface/FileLogger and does not
 *   care about how it is built.
 *
 * What is configured here:
 * - Where logs are stored (Logs directory)
 * - Which channel writes to which file (ChannelMap)
 * - How context is encoded (JSON)
 * - How a log line looks (SimpleLogLineFormatter)
 * - Where/how the line is written (LocalFileWriter)
 * - Which default channel to use when none is provided (DefaultChannelPolicy)
 */
final class LoggerFactory
{
    /**
     * @param string $projectRoot Absolute path to the project root directory
     */
    public function __construct(private readonly string $projectRoot) {}

    /**
     * Builds and returns the configured logger instance.
     *
     * @throws \Design\Logging\Exception\LogWriteException If the Logs directory cannot be created/written
     */
    public function create(): FileLogger
    {
        // 1) Prepare and validate the Logs directory (create it if missing, check permissions)
        $logsDirectory = LogsDirectory::fromPath(
            $this->projectRoot . DIRECTORY_SEPARATOR . 'Logs'
        );

        // 2) Define which file is used for each channel (App => App.log, Errors => Errors.log, etc.)
        // You can replace defaults() with fromArray([...]) if you want custom names.
        $channelMap = ChannelMap::defaults();

        // 3) Build a helper that turns (channel) into the full file path inside the Logs directory.
        $pathResolver = new FilePathResolver($logsDirectory, $channelMap);

        // 4) Choose how "context" arrays are converted to text (JSON here).
        $contextEncoder = new JsonContextEncoder();

        // 5) Choose how a log entry is formatted as a single line of text.
        $lineFormatter = new SimpleLogLineFormatter($contextEncoder);

        // 6) Choose default channels when the caller does not provide one.
        // Example: ERROR/CRITICAL => Errors, others => App
        $channelPolicy = DefaultChannelPolicy::standard();

        // 7) Choose how the log line is physically written (local file append).
        $writer = new LocalFileWriter();

        // 8) Create the final logger, fully configured.
        return new FileLogger(
            clock: new SystemClock(),
            pathResolver: $pathResolver,
            formatter: $lineFormatter,
            writer: $writer,
            channelPolicy: $channelPolicy,
        );
    }
}
