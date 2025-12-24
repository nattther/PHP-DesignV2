<?php
declare(strict_types=1);

namespace Design\Auth\Resolver;

use Design\Auth\Config\AuthConfig;
use Design\Auth\Role\Role;
use Design\Auth\Sso\SsoIdentity;
use Design\Auth\Sso\SsoSessionReader;
use Design\Auth\User\ForbiddenUser;
use Design\Auth\User\SsoUser;
use Design\Auth\User\UserInterface;
use Design\Logging\LoggerInterface;
use Design\Session\SessionManagerInterface;

final class SsoUserResolver
{
    private const AUTH_CHANNEL = 'Auth';

    public function __construct(
        private SsoSessionReader $reader,
        private RoleResolver $roleResolver,
    ) {}

    public function resolve(AuthConfig $authConfig, SessionManagerInterface $session, LoggerInterface $logger): ?UserInterface
    {
        $authLogger = $logger->channel(self::AUTH_CHANNEL);

        $profile = $this->reader->readProfileFromSession($session);
        if ($profile === null) {
            return null;
        }

        $groups   = $this->reader->readGroupsFromSession($session);
        $identity = $this->reader->extractIdentity($profile);

        if (!$identity->isValid()) {
            $this->logMissingId($authLogger, $identity);
            return $this->forbiddenWithoutId($identity);
        }

        $role = $this->roleResolver->fromGroups($groups, $authConfig);

        if ($role === Role::Forbidden) {
            $this->logForbidden($authLogger, $identity);
            return $this->forbiddenWithIdentity($identity);
        }

        $this->logAuthenticated($authLogger, $identity, $role);
        return $this->authenticatedUser($identity, $role);
    }


    

    private function logMissingId(LoggerInterface $authLogger, SsoIdentity $identity): void
    {
        $authLogger->warning('SSO profile found but missing id', [
            'email' => $identity->email,
        ]);
    }

    private function logForbidden(LoggerInterface $authLogger, SsoIdentity $identity): void
    {
        $authLogger->warning('SSO user forbidden (no allowed group match)', [
            'userId' => $identity->id,
            'email'  => $identity->email,
        ]);
    }

    private function logAuthenticated(LoggerInterface $authLogger, SsoIdentity $identity, Role $role): void
    {
        $authLogger->info('SSO user authenticated', [
            'userId' => $identity->id,
            'role'   => $role->value,
        ]);
    }

    private function forbiddenWithoutId(SsoIdentity $identity): ForbiddenUser
    {
        return new ForbiddenUser(id: null, name: $identity->name, email: $identity->email);
    }

    private function forbiddenWithIdentity(SsoIdentity $identity): ForbiddenUser
    {
        return new ForbiddenUser(id: $identity->id, name: $identity->name, email: $identity->email);
    }

    private function authenticatedUser(SsoIdentity $identity, Role $role): SsoUser
    {
        return new SsoUser(role: $role, id: $identity->id, name: $identity->name, email: $identity->email);
    }
}
