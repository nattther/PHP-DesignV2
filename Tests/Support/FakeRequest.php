<?php
declare(strict_types=1);

namespace Design\Tests\Support;

use Design\Http\Request;

/**
 * Small helper to build Request-like objects without touching superglobals.
 * We extend Request by exposing a named constructor using reflection because your Request constructor is private.
 */
final class FakeRequest
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
