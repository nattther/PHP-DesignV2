<?php

declare(strict_types=1);

namespace Design\Security\Csrf;

final class NoopCsrfTokenManager implements CsrfTokenManagerInterface
{
    public function getToken(): string
    {
        return '';
    }

    public function isValid(string $submittedToken): bool
    {
        return true;
    }

    public function validateAndRegenerate(string $submittedToken): bool
    {
        return true;
    }

    public function regenerate(): string
    {
        return '';
    }

    public function clear(): void {}
}
