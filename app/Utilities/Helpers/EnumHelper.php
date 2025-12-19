<?php

namespace App\Utilities\Helpers;


class EnumHelper
{
    public static function getEnumValues(string $enum): array
    {
        return collect($enum::cases())->map(fn($item) => $item->value)->toArray();
    }
    public static function getEnumValuesString(string $enum, string $seperator)
    {
        return collect($enum::cases())->map(fn($item) => $item->value)->implode($seperator);
    }
    public static function getEnumLabels(string $enum): array
    {
        return collect($enum::cases())
            ->map(fn($item) => $item->label())
            ->toArray();
    }
}
