<?php

declare(strict_types=1);

namespace Design\Auth\Resolver;

use Design\Auth\AuthContext;
use Design\Auth\AuthMode;
use Design\Auth\AuthResolverInterface;
use Design\Auth\AuthUser;
use Design\Logging\LoggerInterface;

final class SsoAuthResolver implements AuthResolverInterface
{
    public function __construct(
        private LoggerInterface $logger,
    ) {}

    public function supports(): bool
    {
        return isset($_SESSION['Profile']) && is_array($_SESSION['Profile']);
    }

    public function resolve(): AuthContext
    {
        $profile = $_SESSION['Profile'];

        $username = $profile['email']
            ?? $profile['name']
            ?? null;

        if (!is_string($username) || $username === '') {
            $this->logger->warning('SSO profile present but no usable identifier');
            return $this->publicFallback();
        }

        $this->logger->info('SSO authentication detected', [
            'username' => $username,
        ]);

        return new AuthContext(
            authenticated: true,
            mode: AuthMode::Sso,
            user: new AuthUser(
                username: $username,
                groups: $_SESSION['UserGroups'] ?? [],
                groupsDisplayName: $_SESSION['GroupsDisplayName'] ?? [],
            ),
        );
    }

    private function publicFallback(): AuthContext
    {
        return new AuthContext(
            authenticated: false,
            mode: AuthMode::Public,
            user: null,
        );

    }
}
