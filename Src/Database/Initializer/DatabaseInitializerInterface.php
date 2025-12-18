<?php

declare(strict_types=1);

namespace Design\Database\Initializer;

interface DatabaseInitializerInterface
{
    /**
     * Initializes the database if needed.
     * Must be idempotent.
     */
    public function initialize(): void;
}
