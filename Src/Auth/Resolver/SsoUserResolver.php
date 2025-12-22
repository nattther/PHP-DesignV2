<?php

declare(strict_types=1);

namespace Design\Auth\Resolver;

use Design\Auth\Config\AuthConfig;
use Design\Auth\Role\Role;
use Design\Auth\Sso\SsoSessionReader;
use Design\Auth\User\ForbiddenUser;
use Design\Auth\User\SsoUser;
use Design\Auth\User\UserInterface;
use Design\Logging\LoggerInterface;
use Design\Session\SessionManagerInterface;

final class SsoUserResolver
{
    public function __construct(
        private SsoSessionReader $reader,
        private RoleResolver $roleResolver,
    ) {}

    public function resolve(AuthConfig $authConfig, SessionManagerInterface $session, LoggerInterface $logger): ?UserInterface
    {
        $profile = $this->reader->readProfile($session->get('Profile'));
        if ($profile === null) {
            return null;
        }

        $groups = $this->reader->readGroupsDisplayName($session->get('GroupsDisplayName'));

        $userId = $this->reader->extractUserId($profile);
        $name   = $this->reader->extractName($profile);
        $email  = $this->reader->extractEmail($profile);

        if ($userId === '') {
            $logger->channel('Auth')->warning('SSO profile found but missing id');
            return new ForbiddenUser(); // plus de source
        }

        $role = $this->roleResolver->fromGroups($groups, $authConfig);

        if ($role === Role::Forbidden) {
            $logger->channel('Auth')->warning('SSO user forbidden (no allowed group match)', [
                'userId' => $userId,
                'email' => $email,
            ]);

            return new ForbiddenUser(id: $userId, name: $name, email: $email);
        }

        $logger->channel('Auth')->info('SSO user authenticated', [
            'userId' => $userId,
            'role' => $role->value,
        ]);

        return new SsoUser(role: $role, id: $userId, name: $name, email: $email);
    }
}
