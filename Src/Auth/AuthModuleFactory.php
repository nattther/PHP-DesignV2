<?php
declare(strict_types=1);

namespace Design\Auth;

use Design\Auth\Config\AuthConfig;
use Design\Auth\Resolver\LocalUserResolver;
use Design\Auth\Resolver\RoleResolver;
use Design\Auth\Resolver\SsoUserResolver;
use Design\Auth\Sso\SsoSessionReader;
use Design\Logging\LoggerInterface;
use Design\Session\SessionManagerInterface;

final class AuthModuleFactory
{
    /**
     * Creates the AuthContext for the application (Local -> SSO -> Guest).
     *
     * @param array<string, mixed> $server
     */
    public static function createAuthContext(
        AuthConfig $authConfig,
        SessionManagerInterface $session,
        LoggerInterface $logger,
        array $server,
    ): AuthContext {
        $factory = self::createAuthContextFactory();

        return $factory->create(
            authConfig: $authConfig,
            session: $session,
            logger: $logger,
            server: $server,
        );
    }

    private static function createAuthContextFactory(): AuthContextFactory
    {
        return new AuthContextFactory(
            localUserResolver: new LocalUserResolver(),
            ssoUserResolver: new SsoUserResolver(
                reader: new SsoSessionReader(),
                roleResolver: new RoleResolver(),
            ),
        );
    }
}
