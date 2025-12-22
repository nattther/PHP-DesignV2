<?php

declare(strict_types=1);

namespace Design\Auth;

use Design\Auth\Role\Role;
use Design\Auth\User\UserInterface;

final readonly class AuthContext
{
    public function __construct(
        private UserInterface $user,
    ) {}

    public function user(): UserInterface
    {
        return $this->user;
    }

    public function role(): Role
    {
        return $this->user->role();
    }

    public function isAdmin(): bool
    {
        return $this->role()->isAdmin();
    }

    public function isAllowed(): bool
    {
        return $this->role()->isAtLeastPublic();
    }

    public function isForbidden(): bool
    {
        return $this->role() === Role::Forbidden;
    }
}
