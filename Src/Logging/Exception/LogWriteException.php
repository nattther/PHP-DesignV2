<?php

declare(strict_types=1);

namespace Design\Logging\Exception;

/**
 * Exception thrown when the logging system cannot do its job.
 *
 * Typical reasons:
 * - The Logs directory cannot be created or is not writable
 * - A log file cannot be written (permissions, disk full, invalid path...)
 * - The context cannot be encoded (invalid UTF-8, recursion, etc.)
 *
 * Why a dedicated exception?
 * - So the rest of the application can catch logging-related failures
 *   without accidentally catching unrelated RuntimeExceptions.
 */
final class LogWriteException extends \RuntimeException
{
}
