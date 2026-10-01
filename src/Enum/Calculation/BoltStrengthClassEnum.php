<?php

declare(strict_types=1);

namespace App\Enum\Calculation;

/**
 * Классы прочности болтов с расчётными сопротивлениями.
 */
enum BoltStrengthClassEnum: string
{
    case C3_6 = '3.6';
    case C4_6 = '4.6';
    case C4_8 = '4.8';
    case C5_6 = '5.6';
    case C5_8 = '5.8';
    case C6_6 = '6.6';
    case C6_8 = '6.8';
    case C8_8 = '8.8';
    case C9_8 = '9.8';
    case C10_9 = '10.9';
    case C12_9 = '12.9';

    /** Расчётное сопротивление растяжению, Н/мм² */
    public function tensionResistance(): int
    {
        return match ($this) {
            self::C3_6 => 135,
            self::C4_6, self::C4_8 => 180,
            self::C5_6, self::C5_8 => 225,
            self::C6_6, self::C6_8 => 270,
            self::C8_8, self::C9_8 => 451,
            self::C10_9 => 728,
            self::C12_9 => 854,
        };
    }

    /** Расчётное сопротивление срезу, Н/мм² */
    public function shearResistance(): int
    {
        return match ($this) {
            self::C3_6 => 126,
            self::C4_6, self::C4_8 => 168,
            self::C5_6, self::C5_8 => 210,
            self::C6_6, self::C6_8 => 246,
            self::C8_8 => 332,
            self::C9_8 => 360,
            self::C10_9 => 416,
            self::C12_9 => 427,
        };
    }

    public static function toOptions(): array
    {
        return array_map(
            static fn(self $case): array => [
                'value' => $case->value,
                'label' => $case->value,
                'tensionResistance' => $case->tensionResistance(),
            ],
            self::cases(),
        );
    }
}
