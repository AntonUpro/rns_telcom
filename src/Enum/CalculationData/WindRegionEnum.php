<?php

namespace App\Enum\CalculationData;

enum WindRegionEnum: string
{
    case IA = 'Ia';
    case I = 'I';
    case II = 'II';
    case III = 'III';
    case IV = 'IV';
    case V = 'V';
    case VI = 'VI';
    case VII = 'VII';

    public function pressure(): int
    {
        return match($this) {
            self::IA => 170,
            self::I => 230,
            self::II => 300,
            self::III => 380,
            self::IV => 480,
            self::V => 600,
            self::VI => 730,
            self::VII => 850,
        };
    }

    public function pressureKgPerM(): float
    {
        return round(match($this) {
            self::IA => 170 / 9.81,
            self::I => 230 / 9.81,
            self::II => 300 / 9.81,
            self::III => 380 / 9.81,
            self::IV => 480 / 9.81,
            self::V => 600 / 9.81,
            self::VI => 730 / 9.81,
            self::VII => 850 / 9.81,
        }, 1);
    }

    public function label(): string
    {
        return sprintf('%s (%d Па, %d кг/м²)', $this->value, $this->pressure(), $this->pressureKgPerM());
    }

    public static function choices(): array
    {
        $choices = [];
        foreach (self::cases() as $case) {
            $choices[$case->value] = $case->label();
        }
        return $choices;
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
