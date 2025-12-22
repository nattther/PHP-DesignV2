<?php

declare(strict_types=1);

namespace Design\Auth\User;

use Design\Auth\Role\Role;

final class GuestUser extends AbstractUser
{
    public function __construct()
    {
        parent::__construct(
            role: Role::Guest,
            authenticated: false,
            id: null,
            name: null,
            email: null,
        );
    }
}

