<?php

declare(strict_types=1);

namespace Design\Auth;

use Design\Auth\Resolver\SsoAuthResolver;
use Design\Auth\Resolver\LocalAuthResolver;
use Design\Auth\Resolver\PublicAuthResolver;
use Design\Logging\LoggerInterface;

final class AuthManagerFactory
{
    public static function create(LoggerInterface $logger): AuthManager
    {
        return new AuthManager([
            new SsoAuthResolver($logger->channel('Auth')),
            new LocalAuthResolver($logger->channel('Auth')),
            new PublicAuthResolver($logger->channel('Auth')),
        ]);
    }
}
