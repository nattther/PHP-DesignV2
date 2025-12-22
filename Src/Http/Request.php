<?php
declare(strict_types=1);

namespace Design\Http;

final class Request
{
    /**
     * @param array<string, mixed> $query
     * @param array<string, mixed> $server
     */
    public function __construct(
        private readonly array $query,
        private readonly array $server
    ) {}

    public static function fromGlobals(): self
    {
        return new self($_GET ?? [], $_SERVER ?? []);
    }

    public function queryString(string $key): ?string
    {
        $value = $this->query[$key] ?? null;
        return is_string($value) && $value !== '' ? $value : null;
    }

    public function method(): string
    {
        $method = $this->server['REQUEST_METHOD'] ?? 'GET';
        return is_string($method) ? strtoupper($method) : 'GET';
    }
}
