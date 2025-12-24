<?php
declare(strict_types=1);

namespace Design\Error\ErrorType;

use Design\Error\ExceptionMapperInterface;
use Design\Error\Object\HttpError;
use Throwable;

final class Default500Mapper implements ExceptionMapperInterface
{
    public function supports(Throwable $e): bool
    {
        return true; // fallback
    }

    public function map(Throwable $e): HttpError
    {
        $requestId = bin2hex(random_bytes(6));
        return new HttpError(500, 'Something went wrong. Please try again.', $requestId);
    }
}
