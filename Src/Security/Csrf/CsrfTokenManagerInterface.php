<?php

declare(strict_types=1);

namespace Design\Security\Csrf;

interface CsrfTokenManagerInterface
{
    public function getToken(): string;

    public function isValid(string $submittedToken): bool;


    public function validateAndRegenerate(string $submittedToken): bool;

    public function regenerate(): string;

    public function clear(): void;
}
