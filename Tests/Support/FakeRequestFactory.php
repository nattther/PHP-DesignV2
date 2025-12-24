<?php
declare(strict_types=1);

namespace Design\Tests\Support;

use Design\Http\Request;

final class FakeRequestFactory
{
    /**
     * @param array<string,mixed> $get
     * @param array<string,mixed> $post
     * @param array<string,mixed> $server
     */
    public static function make(array $get = [], array $post = [], array $server = []): Request
    {
        $server = array_merge(['REQUEST_METHOD' => 'GET'], $server);

        $ref = new \ReflectionClass(Request::class);
        $ctor = $ref->getConstructor();
        $ctor?->setAccessible(true);

        /** @var Request $req */
        $req = $ref->newInstanceWithoutConstructor();
        $ctor?->invoke($req, $get, $post, $server);

        return $req;
    }
}
