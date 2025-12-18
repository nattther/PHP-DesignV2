<?php

declare(strict_types=1);

namespace Design\Auth;

final readonly class AuthContext
{
    public function __construct(
        public bool $authenticated,
        public AuthMode $mode,
        public ?AuthUser $user,
    ) {}

    public function isAuthenticated(): bool
    {
        return $this->authenticated;
    }

    public function isSso(): bool
    {
        return $this->mode === AuthMode::Sso;
    }

    public function isLocal(): bool
    {
        return $this->mode === AuthMode::Local;
    }

    public function isPublic(): bool
    {
        return $this->mode === AuthMode::Public;
    }
}
