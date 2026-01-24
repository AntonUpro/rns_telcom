<?php

namespace App\Enum;

enum CalculationTypeEnum: string
{
    case CONCRETE_PILLAR = 'concrete_pillar';
    case METAL_PILLAR = 'metal_pillar';
    case TOWER = 'tower';
    case MAST = 'mast';

    public function label(): string
    {
        return match($this) {
            self::CONCRETE_PILLAR => 'ЖБ столб',
            self::METAL_PILLAR => 'Металлический столб',
            self::TOWER => 'Башня',
            self::MAST => 'Мачта',
        };
    }

    public static function choices(): array
    {
        $choices = [];
        foreach (self::cases() as $case) {
            $choices[$case->label()] = $case->value;
        }
        return $choices;
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
