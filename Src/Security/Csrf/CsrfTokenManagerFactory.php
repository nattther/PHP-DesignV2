<?php
declare(strict_types=1);

namespace Design\Security\Csrf;

use Design\Session\NoopSessionManager;
use Design\Session\SessionManagerInterface;

final class CsrfTokenManagerFactory
{
    public static function create(SessionManagerInterface $session): CsrfTokenManagerInterface
    {
        if ($session instanceof NoopSessionManager) {
            return new NoopCsrfTokenManager();
        }

        return new SessionCsrfTokenManager($session);
    }
}
