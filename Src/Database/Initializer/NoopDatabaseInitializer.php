<?php

declare(strict_types=1);

namespace Design\Database\Initializer;

final class NoopDatabaseInitializer implements DatabaseInitializerInterface
{
    public function initialize(): void {}
}
