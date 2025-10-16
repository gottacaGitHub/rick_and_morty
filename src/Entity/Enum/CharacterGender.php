<?php

namespace App\Entity\Enum;

enum CharacterGender: string
{
    case FEMALE = 'Female';
    case MALE = 'Male';
    case GENDERLESS = 'Genderless';
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
