<?php
declare(strict_types=1);

namespace Design\Error\ErrorType;

use Design\Error\ExceptionMapperInterface;
use Design\Error\Object\HttpError;
use Design\Security\Exception\MethodNotAllowed;
use Throwable;

final class MethodNotAllowedMapper implements ExceptionMapperInterface
{
    public function supports(Throwable $e): bool
    {
        return $e instanceof MethodNotAllowed;
    }

    public function map(Throwable $e): HttpError
    {
        return new HttpError(405, $e->getMessage() ?: 'Method not allowed.');
    }
}
