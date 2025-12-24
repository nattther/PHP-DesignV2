<?php
declare(strict_types=1);

namespace Design\Http;

final readonly class Request
{
    /**
     * @param array<string, mixed> $get
     * @param array<string, mixed> $post
     * @param array<string, mixed> $server
     */
    private function __construct(
        private array $get,
        private array $post,
        private array $server,
    ) {}

    public static function fromGlobals(): self
    {
        return new self($_GET, $_POST, $_SERVER);
    }

    public function method(): string
    {
        $method = $this->server['REQUEST_METHOD'] ?? 'GET';
        return is_string($method) && $method !== '' ? strtoupper($method) : 'GET';
    }

    public function isPost(): bool
    {
        return $this->method() === 'POST';
    }

    public function queryString(string $key): ?string
    {
        $value = $this->get[$key] ?? null;
        return is_string($value) && $value !== '' ? $value : null;
    }

    public function postString(string $key): ?string
    {
        $value = $this->post[$key] ?? null;
        return is_string($value) && $value !== '' ? $value : null;
    }
}
