<?php

declare(strict_types=1);

namespace Design\Auth\User;

use Design\Auth\Role\Role;

final class ForbiddenUser extends AbstractUser
{
    public function __construct(?string $id = null, ?string $name = null, ?string $email = null)
    {
        parent::__construct(
            role: Role::Forbidden,
            authenticated: $id !== null,
            id: $id,
            name: $name,
            email: $email,
        );
    }
}