<?php

declare(strict_types=1);

namespace Design\Auth\Resolver;

use Design\Auth\AuthContext;
use Design\Auth\AuthMode;
use Design\Auth\AuthResolverInterface;
use Design\Logging\LoggerInterface;

final class PublicAuthResolver implements AuthResolverInterface
{
    public function __construct(
        private LoggerInterface $logger,
    ) {}

    public function supports(): bool
    {
        return true;
    }

    public function resolve(): AuthContext
    {
        $this->logger->info('Public access (no authentication)');

        return new AuthContext(
            authenticated: false,
            mode: AuthMode::Public,
            user: null,
        );
    }
}
