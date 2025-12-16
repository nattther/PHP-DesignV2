<?php

declare(strict_types=1);

namespace Design\Logging\Writer;

/**
 * Writes the formatted log line to a destination.
 *
 * In this project, the main destination is a local file (LocalFileWriter),
 * but having an interface makes it easy to:
 * - write to a different storage later (database, remote service, memory for tests)
 * - test the logger without touching the filesystem
 */
interface FileWriterInterface
{
    /**
     * Appends content at the end of a log file.
     *
     * Notes:
     * - The caller usually provides a complete line including PHP_EOL.
     * - Implementations should throw an exception if writing fails.
     *
     * @param string $filePath Full path to the log file (ex: "/.../Logs/App.log")
     * @param string $content  The content to append (often one formatted log line)
     */
    public function append(string $filePath, string $content): void;
}
