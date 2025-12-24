<?php
declare(strict_types=1);

namespace Design\Error;

use Design\Error\Object\HttpError;
use Throwable;

interface ExceptionMapperInterface
{
    public function supports(Throwable $e): bool;

    public function map(Throwable $e): HttpError;
}
