<?php

declare(strict_types=1);

namespace Design\Auth\User;

use Design\Auth\Role\Role;

final class SsoUser extends AbstractUser
{
    public function __construct(Role $role, string $id, ?string $name, ?string $email)
    {
        parent::__construct(
            role: $role,
            authenticated: true,
            id: $id,
            name: $name,
            email: $email,
        );
    }
}
