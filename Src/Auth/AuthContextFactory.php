<?php
declare(strict_types=1);

namespace Design\Auth;

use Design\Auth\Config\AuthConfig;
use Design\Auth\Resolver\LocalUserResolver;
use Design\Auth\Resolver\SsoUserResolver;
use Design\Auth\User\GuestUser;
use Design\Logging\LoggerInterface;
use Design\Session\SessionManagerInterface;

final class AuthContextFactory
{
    public function __construct(
        private LocalUserResolver $localUserResolver,
        private SsoUserResolver $ssoUserResolver,
    ) {}

    /**
     * @param array<string, mixed> $server
     */
    public function create(
        AuthConfig $authConfig,
        SessionManagerInterface $session,
        LoggerInterface $logger,
        array $server,
    ): AuthContext 
    
    {
        $localUser = $this->localUserResolver->resolve($authConfig, $logger, $server);
        if ($localUser !== null) {
            return new AuthContext($localUser);
        }

        $ssoUser = $this->ssoUserResolver->resolve($authConfig, $session, $logger);
        if ($ssoUser !== null) {
            return new AuthContext($ssoUser);
        }

        return new AuthContext(new GuestUser());
    }
}
