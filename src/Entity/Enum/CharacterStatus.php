<?php

namespace App\Entity\Enum;

enum CharacterStatus: string
{
    case ALIVE = 'Alive';
    case DEAD = 'Dead';
    case UNKNOWN = 'unknown';

    public static function values(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }

    public static function isValid(string $value): bool
    {
        return in_array($value, self::values());
    }
}
