<?php
declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use Design\Http\Request;
use Design\Http\Response;
use Design\Http\Exception\HttpException;
use Design\Kernel\KernelFactory;
use Design\Presentation\View\CompositePhpRenderer;
use Design\Routing\ViewPathResolver;
use Design\Security\Access\AccessChecker;

$kernel = KernelFactory::createForFront($_SERVER);

$request = Request::fromGlobals();

$projectRoot = $kernel->settings()->paths()->rootPath;

$publicViewsRoot = $projectRoot . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'public_views';
$adminViewsRoot  = $projectRoot . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'admin_views';

$resolver = new ViewPathResolver($publicViewsRoot, $adminViewsRoot);
$renderer = new CompositePhpRenderer();
$access   = new AccessChecker($kernel->auth());

try {
    $resolvedView = $resolver->resolve($request);
    $access->assertCanAccess($resolvedView);

    $data = [
        'kernel' => $kernel,
        'auth'   => $kernel->auth(),
        'user'   => $kernel->auth()->user(),
        'flash'  => $kernel->flash(),
        'csrf'   => $kernel->csrf(),
        'request'=> $request,
    ];

    $html = $renderer->render($resolvedView, $data);
    (new Response($html, 200))->send();

} catch (HttpException $e) {
    $kernel->logger()->warning('HTTP error', ['status' => $e->statusCode(), 'message' => $e->getMessage()]);

    $errorView = match ($e->statusCode()) {
        403 => $publicViewsRoot . DIRECTORY_SEPARATOR . 'errors' . DIRECTORY_SEPARATOR . 'forbidden.php',
        404 => $publicViewsRoot . DIRECTORY_SEPARATOR . 'errors' . DIRECTORY_SEPARATOR . 'not_found.php',
        default => $publicViewsRoot . DIRECTORY_SEPARATOR . 'errors' . DIRECTORY_SEPARATOR . 'error.php',
    };

    // On rend une page d’erreur via le layout public (navbar/footer public)
    $fallbackResolved = new \Design\Routing\ResolvedView(
        area: \Design\Routing\ViewArea::Public,
        pageFilePath: $errorView,
        navbarFilePath: $publicViewsRoot . DIRECTORY_SEPARATOR . '_navbar.php',
        footerFilePath: $publicViewsRoot . DIRECTORY_SEPARATOR . '_footer.php',
    );

    $html = $renderer->render($fallbackResolved, ['kernel' => $kernel, 'message' => $e->getMessage()]);
    (new Response($html, $e->statusCode()))->send();

} catch (Throwable $e) {
    $kernel->logger()->error('Unhandled error', ['exception' => $e]);

    $errorView = $publicViewsRoot . DIRECTORY_SEPARATOR . 'errors' . DIRECTORY_SEPARATOR . 'error.php';

    $fallbackResolved = new \Design\Routing\ResolvedView(
        area: \Design\Routing\ViewArea::Public,
        pageFilePath: $errorView,
        navbarFilePath: $publicViewsRoot . DIRECTORY_SEPARATOR . '_navbar.php',
        footerFilePath: $publicViewsRoot . DIRECTORY_SEPARATOR . '_footer.php',
    );

    $html = $renderer->render($fallbackResolved, ['message' => 'An unexpected error occurred.']);
    (new Response($html, 500))->send();
}
