<?php

declare(strict_types=1);

namespace Design\Auth\User;

use Design\Auth\Role\Role;

abstract class AbstractUser implements UserInterface
{
    public function __construct(
        private Role $role,
        private bool $authenticated,
        private ?string $id,
        private ?string $name,
        private ?string $email,
    ) {}

    public function role(): Role { return $this->role; }
    public function isAuthenticated(): bool { return $this->authenticated; }
    public function id(): ?string { return $this->id; }
    public function name(): ?string { return $this->name; }
    public function email(): ?string { return $this->email; }
}