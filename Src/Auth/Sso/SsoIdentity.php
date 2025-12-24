<?php
declare(strict_types=1);

namespace Design\Auth\Sso;

final readonly class SsoIdentity
{
    public function __construct(
        public string $id,
        public ?string $name,
        public ?string $email,
    ) {}

    public function isValid(): bool
    {
        return $this->id !== '';
    }
}
