<?php

declare(strict_types=1);

namespace Design\Auth;

final readonly class AuthUser
{
    public function __construct(
        public string $username,
        public array $groups,
        public array $groupsDisplayName,
    ) {}
}
