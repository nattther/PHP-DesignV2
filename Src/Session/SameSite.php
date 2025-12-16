<?php

declare(strict_types=1);

enum SameSite: string
{
    case Lax = 'Lax';
    case Strict = 'Strict';
    case None = 'None';

    public static function fromString(string $value): self
    {
        return match (strtolower(trim($value))) {
            'lax'    => self::Lax,
            'strict' => self::Strict,
            'none'   => self::None,
            default  => throw new InvalidArgumentException('Invalid SameSite value. Use Lax, Strict or None.'),
        };
    }
}
