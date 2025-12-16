<?php

declare(strict_types=1);

namespace Design\Logging\Writer;

use Design\Logging\Exception\LogWriteException;

/**
 * Writes log lines to a file on the local filesystem.
 *
 * Behavior:
 * - Always appends to the end of the file (keeps existing logs)
 * - Uses a file lock to reduce the risk of mixed lines when multiple requests
 *   write at the same time
 *
 * This class does not format anything and does not choose the file path.
 * It only receives a path + text and tries to write it.
 */
final class LocalFileWriter implements FileWriterInterface
{
    /**
     * Appends the given content at the end of the given file.
     *
     * @throws LogWriteException If the file cannot be written.
     */
    public function append(string $filePath, string $content): void
    {
        $result = @file_put_contents($filePath, $content, FILE_APPEND | LOCK_EX);

        if ($result === false) {
            throw new LogWriteException("Failed to write log line to: {$filePath}");
        }
    }
}
