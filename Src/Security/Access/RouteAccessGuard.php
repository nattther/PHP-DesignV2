<?php
declare(strict_types=1);

namespace Design\Security\Access;

use Design\Auth\AuthContext;
use Design\Routing\ResolvedRoute;
use Design\Security\Exception\AccessDenied;

final readonly class RouteAccessGuard
{
    public function __construct(private AuthContext $auth) {}

    public function assertAllowed(ResolvedRoute $route): void
    {
        // If SSO says forbidden, block everything (except maybe login/home if you want)
        if ($this->auth->isForbidden()) {
            throw new AccessDenied('Your account is not allowed to access this application.');
        }

        // Admin pages must be admin
        if ($route->isAdminView() && !$this->auth->isAdmin()) {
            throw new AccessDenied('Admin access required.');
        }

    }
}
