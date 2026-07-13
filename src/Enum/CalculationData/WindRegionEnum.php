<?php

namespace App\Enum\CalculationData;

enum WindRegionEnum: string
{
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
            self::I => 230 / 9.81,
            self::II => 30 / 9.81,
            self::III => 38 / 9.81,
            self::IV => 48 / 9.81,
            self::V => 60 / 9.81,
            self::VI => 73 / 9.81,
            self::VII => 85 / 9.81,
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
