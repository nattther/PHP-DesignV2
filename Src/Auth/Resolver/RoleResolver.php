<?php

declare(strict_types=1);

namespace Design\Auth\Resolver;

use Design\Auth\Config\AuthConfig;
use Design\Auth\Role\Role;

final class RoleResolver
{

    public function fromGroups(array $userGroupsByName, AuthConfig $authConfig): Role

    {
        $hasPublic = false;

        foreach ($userGroupsByName as $groupName => $_) {
            if ($authConfig->isAdminGroup($groupName)) {
                return Role::Admin;
            }

            if ($authConfig->isPublicGroup($groupName)) {
                $hasPublic = true;
            }
        }

        return $hasPublic ? Role::Public : Role::Forbidden;
    }
}
