<?php

namespace App\Enum\CalculationData;

enum IcingRegionEnum: string
{
    case I = 'I';
    case II = 'II';
    case III = 'III';
    case IV = 'IV';
    case V = 'V';

    public function label(): string
    {
        return match($this) {
            self::I => 'I район',
            self::II => 'II район',
            self::III => 'III район',
            self::IV => 'IV район',
            self::V => 'V район',
        };
    }

    public function description(): string
    {
        return match($this) {
            self::I => 'Слабый гололед',
            self::II => 'Умеренный гололед',
            self::III => 'Сильный гололед',
            self::IV => 'Очень сильный гололед',
            self::V => 'Экстремальный гололед',
        };
    }

    public function thicknessMm(): array
    {
        return match($this) {
            self::I => ['min' => 0, 'max' => 5],
            self::II => ['min' => 5, 'max' => 10],
            self::III => ['min' => 10, 'max' => 15],
            self::IV => ['min' => 15, 'max' => 20],
            self::V => ['min' => 20, 'max' => 25],
        };
    }

    public static function choices(): array
    {
        $choices = [];
        foreach (self::cases() as $case) {
            $choices[$case->label() . ' - ' . $case->description()] = $case->value;
        }
        return $choices;
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
