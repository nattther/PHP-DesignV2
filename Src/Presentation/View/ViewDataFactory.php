<?php
declare(strict_types=1);

namespace Design\Presentation\View;

use Design\Http\Request;
use Design\Kernel\Kernel;

final class ViewDataFactory
{
    /**
     * Builds the default data available in every PHP view.
     *
     * @return array<string, mixed>
     */
    public function create(Kernel $kernel, Request $request): array
    {
        return [
            'kernel'  => $kernel,
            'auth'    => $kernel->auth(),
            'user'    => $kernel->auth()->user(),
            'flash'   => $kernel->flash(),
            'csrf'    => $kernel->csrf(),
            'request' => $request,
        ];
    }
}
