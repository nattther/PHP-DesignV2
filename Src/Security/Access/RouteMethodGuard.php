<?php
declare(strict_types=1);

namespace Design\Security\Access;

use Design\Http\Request;
use Design\Routing\ResolvedRoute;
use Design\Security\Exception\MethodNotAllowed;

final readonly class RouteMethodGuard
{
    public function assertAllowedMethod(Request $request, ResolvedRoute $route): void
    {
        // ajax => POST only
        if ($route->isAjax() && !$request->isPost()) {
            throw new MethodNotAllowed('AJAX endpoints require POST.');
        }

        // action => GET only
        if ($route->isAction() && $request->isPost()) {
            throw new MethodNotAllowed('Actions require GET.');
        }
    }
}
