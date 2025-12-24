<?php
declare(strict_types=1);

namespace Design\Error\ErrorType;

use Design\Error\ExceptionMapperInterface;
use Design\Error\Object\HttpError;
use Design\Security\Exception\AccessDenied;
use Throwable;

final class AccessDeniedMapper implements ExceptionMapperInterface
{
    public function supports(Throwable $e): bool
    {
        return $e instanceof AccessDenied;
    }

    public function map(Throwable $e): HttpError
    {
        /** @var AccessDenied $e */
        return new HttpError(403, $e->getMessage() ?: 'Access denied.');
    }
}
