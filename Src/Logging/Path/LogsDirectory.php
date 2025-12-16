<?php

declare(strict_types=1);

namespace Design\Logging\Path;

use Design\Logging\Exception\LogWriteException;

/**
 * Represents the directory where log files are stored.
 *
 * This object is created once (usually in the factory) and immediately checks:
 * - The path is not a file
 * - The directory exists (or can be created)
 * - The directory is writable
 *
 * The goal is to fail early at application start, instead of discovering
 * "cannot write logs" later at runtime.
 */
final readonly class LogsDirectory
{
    /**
     * Normalized directory path (no trailing slash).
     */
    private function __construct(public string $path) {}

    /**
     * Creates a LogsDirectory from a path and validates it.
     *
     * @throws LogWriteException If the directory is invalid or not writable.
     */
    public static function fromPath(string $path): self
    {
        // If the given path already exists but is a file, it cannot be used as a directory.
        if (is_file($path)) {
            throw new LogWriteException("Logs directory path points to a file: {$path}");
        }

        // Create the directory if it does not exist yet.
        if (!is_dir($path)) {
            if (!mkdir($path, 0775, true) && !is_dir($path)) {
                throw new LogWriteException("Unable to create logs directory: {$path}");
            }
        }

        // Make sure we can write log files inside it.
        if (!is_writable($path)) {
            throw new LogWriteException("Logs directory is not writable: {$path}");
        }

        // Normalize the path to avoid double separators later.
        return new self(rtrim($path, DIRECTORY_SEPARATOR));
    }
}
