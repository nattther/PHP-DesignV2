<?php

declare(strict_types=1);

namespace Design\Auth\User;

use Design\Auth\Role\Role;

interface UserInterface
{
    public function role(): Role;

    public function isAuthenticated(): bool;

    public function id(): ?string;

    public function name(): ?string;

    public function email(): ?string;
}
