<?php

declare(strict_types=1);

namespace Design\Auth\User;

use Design\Auth\Role\Role;

final class LocalUser extends AbstractUser
{
    public function __construct(Role $role)
    {
        parent::__construct(
            role: $role,
            authenticated: true,
            id: 'local',
            name: 'Local Developer',
            email: null,
        );
    }
}