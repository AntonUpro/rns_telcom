<?php

namespace App\Enum\Equipment;

enum EquipmentGroupEnum: string
{
    case EXIST = 'exist';
    case PLAIN = 'plain';
    case DISMANT = 'dismant';

    public function label(): string
    {
        return match($this) {
            self::EXIST => 'Существующее',
            self::PLAIN => 'Планируемое',
            self::DISMANT => 'Демонтируемое',
        };
    }

    /**
     * @return string[]
     */
    public static function forCalculation(): array
    {
        return [self::EXIST->value, self::PLAIN->value];
    }
}
