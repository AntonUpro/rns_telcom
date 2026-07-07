<?php

declare(strict_types=1);

namespace App\Enum\Calculation;

/**
 * Гибкость элемента по таблице 32 СП 16.13330.2017.
 * Пояса используют варианты 1а/1б, остальные элементы (раскосы и т.п.) — 2а/2б.
 */
enum FlexibilityTypeEnum: string
{
    case ONE_A = '1a';
    case ONE_B = '1b';
    case TWO_A = '2a';
    case TWO_B = '2b';

    public function label(): string
    {
        return match ($this) {
            self::ONE_A => '1а',
            self::ONE_B => '1б',
            self::TWO_A => '2а',
            self::TWO_B => '2б',
        };
    }

    public static function optionsForBelt(): array
    {
        return array_map(
            fn(self $case) => ['value' => $case->value, 'label' => $case->label()],
            [self::ONE_A, self::ONE_B]
        );
    }

    public static function optionsForOther(): array
    {
        return array_map(
            fn(self $case) => ['value' => $case->value, 'label' => $case->label()],
            [self::TWO_A, self::TWO_B]
        );
    }
}
