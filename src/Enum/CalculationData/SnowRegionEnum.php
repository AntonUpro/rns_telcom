<?php

namespace App\Enum\CalculationData;

enum SnowRegionEnum: string
{
    case I = 'I';
    case II = 'II';
    case III = 'III';
    case IV = 'IV';
    case V = 'V';
    case VI = 'VI';
    case VII = 'VII';
    case VIII = 'VIII';

    public function label(): string
    {
        return sprintf('%s снеговой район', $this->value);
    }

    public function snowLoad(): float
    {
        return match($this) {
            self::I => 0.8,
            self::II => 1.2,
            self::III => 1.8,
            self::IV => 2.4,
            self::V => 3.2,
            self::VI => 4.0,
            self::VII => 4.8,
            self::VIII => 5.6,
        };
    }

    public function description(): string
    {
        return sprintf('Снеговая нагрузка: %s кПа', $this->snowLoad());
    }

    public static function choices(): array
    {
        $choices = [];
        foreach (self::cases() as $case) {
            $choices[$case->label() . ' (' . $case->snowLoad() . ' кПа)'] = $case->value;
        }
        return $choices;
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
