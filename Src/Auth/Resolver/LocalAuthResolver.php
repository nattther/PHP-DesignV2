<?php

declare(strict_types=1);

namespace Design\Auth\Resolver;

use Design\Auth\AuthContext;
use Design\Auth\AuthMode;
use Design\Auth\AuthResolverInterface;
use Design\Auth\AuthUser;
use Design\Logging\LoggerInterface;

final class LocalAuthResolver implements AuthResolverInterface
{
    public function __construct(
        private LoggerInterface $logger,
    ) {}

public function supports(): bool
{
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $addr = $_SERVER['REMOTE_ADDR'] ?? '';

    return $addr === '127.0.0.1'
        || $addr === '::1'
        || str_starts_with($host, 'localhost');
}


    public function resolve(): AuthContext
    {
        $this->logger->info('Local authentication (dev mode)');

        return new AuthContext(
            authenticated: true,
            mode: AuthMode::Local,
            user: new AuthUser(
                username: 'local',
                groups: ['LOCAL'],
                groupsDisplayName: ['Local'],
            ),
        );
    }
}
