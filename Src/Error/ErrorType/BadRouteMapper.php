<?php
declare(strict_types=1);

namespace Design\Error\ErrorType;

use Design\Error\ExceptionMapperInterface;
use Design\Error\Object\HttpError;
use Design\Routing\Exception\BadRoute;
use Throwable;

final class BadRouteMapper implements ExceptionMapperInterface
{
    public function supports(Throwable $e): bool
    {
        return $e instanceof BadRoute;
    }

    public function map(Throwable $e): HttpError
    {
        return new HttpError(400, 'Invalid request.');
    }
}
