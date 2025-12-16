<?php

declare(strict_types=1);

final class SessionSettings
{
    public function __construct(
        public readonly int $idleTimeoutSeconds,
        public readonly int $regenerateIntervalSeconds,
    ) {
        if ($this->idleTimeoutSeconds < 0) {
            throw new InvalidArgumentException('idleTimeoutSeconds must be >= 0.');
        }
        if ($this->regenerateIntervalSeconds < 0) {
            throw new InvalidArgumentException('regenerateIntervalSeconds must be >= 0.');
        }
    }

    /**
     * @param array<string, mixed> $config
     */
    public static function fromArray(array $config): self
    {
        $idle = self::asInt($config['idle_timeout'] ?? 20 * 60);
        $regen = self::asInt($config['regen_interval'] ?? 10 * 60);

        return new self($idle, $regen);
    }

    private static function asInt(mixed $value): int
    {
        if (is_int($value)) {
            return $value;
        }
        if (is_string($value) && preg_match('/^-?\d+$/', $value) === 1) {
            return (int)$value;
        }

        throw new InvalidArgumentException('Expected integer config value.');
    }
}
