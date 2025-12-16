<?php

declare(strict_types=1);

final class CookieSettings
{
    public function __construct(
        public readonly string $path,
        public readonly string $domain,
        public readonly bool $httpOnly,
        public readonly SameSite $sameSite,
        public readonly bool|null $secure, // null => auto-detect HTTPS
    ) {
        if ($this->path === '') {
            throw new InvalidArgumentException('Cookie path cannot be empty.');
        }
        // domain may be empty => OK (default host)
    }

    /**
     * @param array<string, mixed> $config
     */
    public static function fromArray(array $config): self
    {
        $path = self::asString($config['path'] ?? '/');
        $domain = self::asString($config['domain'] ?? '');
        $httpOnly = self::asBool($config['httponly'] ?? true);

        $sameSiteRaw = $config['samesite'] ?? 'Lax';
        $sameSite = SameSite::fromString(self::asString($sameSiteRaw));

        $secure = self::asBoolOrNull($config['secure'] ?? null);

        return new self(
            path: $path,
            domain: $domain,
            httpOnly: $httpOnly,
            sameSite: $sameSite,
            secure: $secure,
        );
    }

    private static function asString(mixed $value): string
    {
        if (!is_string($value)) {
            throw new InvalidArgumentException('Expected string config value.');
        }
        return $value;
    }

    private static function asBool(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }
        if (is_int($value)) {
            return $value === 1;
        }
        if (is_string($value)) {
            $v = strtolower(trim($value));
            if (in_array($v, ['1', 'true', 'yes', 'on'], true)) return true;
            if (in_array($v, ['0', 'false', 'no', 'off'], true)) return false;
        }

        throw new InvalidArgumentException('Expected boolean config value.');
    }

    private static function asBoolOrNull(mixed $value): ?bool
    {
        if ($value === null) {
            return null;
        }
        return self::asBool($value);
    }
}
