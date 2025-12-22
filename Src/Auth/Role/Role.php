<?php

declare(strict_types=1);

namespace Design\Auth\Role;

enum Role: string
{
    case Admin = 'admin';
    case Public = 'public';
    case Guest = 'guest';
    case Forbidden = 'forbidden';

    public function isAtLeastPublic(): bool
    {
        return $this === self::Public || $this === self::Admin;
    }

    public function isAdmin(): bool
    {
        return $this === self::Admin;
    }
}
