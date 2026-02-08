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

    public function pressureKgPerM(): int
    {
        return match($this) {
            self::I => 23,
            self::II => 30,
            self::III => 38,
            self::IV => 48,
            self::V => 60,
            self::VI => 73,
            self::VII => 85,
        };
    }

    public function label(): string
    {
        return sprintf('%s (%d Па)', $this->value, $this->pressure());
    }

    public static function choices(): array
    {
        $choices = [];
        foreach (self::cases() as $case) {
            $choices[$case->label()] = $case->value;
        }
        return $choices;
    }
}
