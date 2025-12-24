<?php
declare(strict_types=1);

namespace Design\Security\Csrf;

use Design\Http\Request;
use Design\Security\Exception\CsrfInvalid;

final readonly class CsrfGuard
{
    public function __construct(private CsrfTokenManagerInterface $csrf) {}

    public function assertValidForPost(Request $request): void
    {
        if (!$request->isPost()) {
            return;
        }

        $token = $this->extractToken($request);

        // validate + rotate (prevents replay)
        if (!$this->csrf->validateAndRegenerate($token)) {
            throw new CsrfInvalid('Invalid CSRF token.');
        }
    }

    private function extractToken(Request $request): string
    {
        // 1) AJAX header (recommended)
        $header = $request->header('X-CSRF-Token');
        if ($header !== null && $header !== '') {
            return $header;
        }

        // 2) Standard form field
        $post = $request->postString('_csrf');
        if ($post !== null && $post !== '') {
            return $post;
        }

        return '';
    }
}
