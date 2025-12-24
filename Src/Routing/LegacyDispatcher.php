<?php
declare(strict_types=1);

namespace Design\Routing;

use Design\Http\Request;
use Design\Path\AppPaths;
use Design\Routing\Exception\BadRoute;
use Design\Routing\Exception\RouteNotFound;

final readonly class LegacyDispatcher
{
    public function __construct(private AppPaths $paths){}

    public function dispatch(Request $request): void
    {
        if ($request->isPost() && ($ajax = $request->postString('ajax')) !== null) {
            require $this->file($this->paths->ajaxDir(), $ajax);
            return;
        }

        if (($action = $request->queryString('action')) !== null) {
            require $this->file($this->paths->controllerDir(), $action);
            return;
        }

        if (($adminPage = $request->queryString('admin_page')) !== null) {
            require $this->file($this->paths->adminViewsDir(), $adminPage);
            return;
        }

        if (($page = $request->queryString('page')) !== null) {
            require $this->file($this->paths->publicViewsDir(), $page);
            return;
        }

        if (($route = $request->queryString('route')) !== null) {
            throw new RouteNotFound("No route matched: {$route}");
        }

        require $this->file($this->paths->publicViewsDir(), 'home');
    }

    private function file(string $dir, string $name): string
    {
        $safe = $this->sanitize($name);
        $path = rtrim($dir, '/\\') . DIRECTORY_SEPARATOR . $safe . '.php';

        if (!is_file($path)) {
            throw new RouteNotFound("File not found: {$path}");
        }

        return $path;
    }

    private function sanitize(string $name): string
    {
        $name = trim($name);

        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $name)) {
            throw new BadRoute("Invalid route name: {$name}");
        }

        return $name;
    }
}
