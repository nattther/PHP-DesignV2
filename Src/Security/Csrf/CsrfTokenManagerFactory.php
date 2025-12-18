<?php

declare(strict_types=1);

namespace Design\Security\Csrf;

use Design\Kernel\KernelContext;
use Design\Session\SessionManagerInterface;

final class CsrfTokenManagerFactory
{
    public static function create(
        KernelContext $context,
        SessionManagerInterface $session,
    ): CsrfTokenManagerInterface {
        if ($context === KernelContext::Cli || $context === KernelContext::Job) {
            return new NoopCsrfTokenManager();
        }

        return new SessionCsrfTokenManager($session);
    }
}
