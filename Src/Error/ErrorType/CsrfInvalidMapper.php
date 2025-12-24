<?php
declare(strict_types=1);

namespace Design\Error\ErrorType;

use Design\Error\ExceptionMapperInterface;
use Design\Error\Object\HttpError;
use Design\Security\Exception\CsrfInvalid;
use Throwable;

final class CsrfInvalidMapper implements ExceptionMapperInterface
{
    public function supports(Throwable $e): bool
    {
        return $e instanceof CsrfInvalid;
    }

    public function map(Throwable $e): HttpError
    {
        // 419 = "Page Expired" (Laravel style). If you prefer: use 403.
        return new HttpError(419, 'Your session expired. Please refresh and try again.');
    }
}
