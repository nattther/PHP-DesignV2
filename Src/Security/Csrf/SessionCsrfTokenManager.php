<?php

declare(strict_types=1);

namespace Design\Security\Csrf;

use Design\Session\SessionManagerInterface;

final class SessionCsrfTokenManager implements CsrfTokenManagerInterface
{
    private const SESSION_KEY = '__csrf_token__';

    public function __construct(
        private readonly SessionManagerInterface $session,
    ) {}

    public function getToken(): string
    {
        $token = $this->session->get(self::SESSION_KEY);

        if (is_string($token) && $token !== '') {
            return $token;
        }

        return $this->regenerate();
    }

    public function isValid(string $submittedToken): bool
    {
        if ($submittedToken === '') {
            return false;
        }

        $stored = $this->session->get(self::SESSION_KEY);

        return is_string($stored)
            && hash_equals($stored, $submittedToken);
    }

    public function validateAndRegenerate(string $submittedToken): bool
    {
        if (!$this->isValid($submittedToken)) {
            return false;
        }

        $this->regenerate();

        return true;
    }

    public function regenerate(): string
    {
        $token = bin2hex(random_bytes(32));
        $this->session->set(self::SESSION_KEY, $token);

        return $token;
    }

    public function clear(): void
    {
        $this->session->remove(self::SESSION_KEY);
    }
}
