<?php

declare(strict_types=1);

namespace Design\Auth\Resolver;

use Design\Auth\Config\AuthConfig;
use Design\Auth\Role\Role;

final class RoleResolver
{
    /**
     * @param array<string,int> $groupsDisplayName
     */
    public function fromGroups(array $groupsDisplayName, AuthConfig $authConfig): Role
    {
        foreach ($authConfig->ssoAdminGroups as $g) {
            if (isset($groupsDisplayName[$g])) {
                return Role::Admin;
            }
        }

        foreach ($authConfig->ssoPublicGroups as $g) {
            if (isset($groupsDisplayName[$g])) {
                return Role::Public;
            }
        }

        return Role::Forbidden;
    }
}
