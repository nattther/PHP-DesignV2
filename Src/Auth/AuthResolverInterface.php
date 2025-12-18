<?php

declare(strict_types=1);

namespace Design\Auth;

interface AuthResolverInterface
{
    public function supports(): bool;

    public function resolve(): AuthContext;
}
