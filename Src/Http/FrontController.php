<?php
declare(strict_types=1);

namespace Design\Http;

use Design\Http\Exception\HttpException;
use Design\Kernel\Kernel;
use Design\Presentation\View\AdminLayoutDecorator;
use Design\Presentation\View\CompositePhpRenderer;
use Design\Presentation\View\ViewDataFactory;
use Design\Routing\ResolvedView;
use Design\Routing\ViewArea;
use Design\Routing\ViewPathResolver;
use Design\Security\Access\AccessChecker;
use Design\Settings\Settings;
use Throwable;

final class FrontController
{
    public function __construct(
        private readonly Kernel $kernel,
        private readonly ViewPathResolver $resolver,
        private readonly CompositePhpRenderer $renderer,
        private readonly AccessChecker $accessChecker,
        private readonly AdminLayoutDecorator $layoutDecorator,
        private readonly ViewDataFactory $viewDataFactory,
        private readonly Settings $settings,
    ) {}

    /**
     * Handles the request and always returns a Response (no echo here).
     */
    public function handle(Request $request): Response
    {
        $views = $this->settings->views();
        $data  = $this->viewDataFactory->create($this->kernel, $request);

        try {
            $resolvedView = $this->resolver->resolve($request);
            $this->accessChecker->assertCanAccess($resolvedView);

            // Admin => admin navbar/footer even on public pages
            $resolvedView = $this->layoutDecorator->decorate($resolvedView);

            $html = $this->renderer->render($resolvedView, $data);

            return new Response($html, 200);

        } catch (HttpException $e) {
            $this->kernel->logger()->warning('HTTP error', [
                'status' => $e->statusCode(),
                'message' => $e->getMessage(),
            ]);

            $errorFile = match ($e->statusCode()) {
                403 => $views->errorViewPath('forbidden'),
                404 => $views->errorViewPath('not_found'),
                default => $views->errorViewPath('error'),
            };

            $fallbackResolved = new ResolvedView(
                area: ViewArea::Public,
                pageFilePath: $errorFile,
                navbarFilePath: $views->publicNavbarPath(),
                footerFilePath: $views->publicFooterPath(),
            );

            $html = $this->renderer->render(
                $fallbackResolved,
                array_merge($data, ['message' => $e->getMessage()])
            );

            return new Response($html, $e->statusCode());

        } catch (Throwable $e) {
            $this->kernel->logger()->error('Unhandled error', ['exception' => $e]);

            $fallbackResolved = new ResolvedView(
                area: ViewArea::Public,
                pageFilePath: $views->errorViewPath('error'),
                navbarFilePath: $views->publicNavbarPath(),
                footerFilePath: $views->publicFooterPath(),
            );

            $html = $this->renderer->render(
                $fallbackResolved,
                array_merge($data, ['message' => 'An unexpected error occurred.'])
            );

            return new Response($html, 500);
        }
    }
}
