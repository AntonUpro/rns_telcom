<?php

declare(strict_types=1);

namespace App\Enum\Equipment;

enum EquipmentTypeEnum: string
{
    case RRL = 'rrl';
    case PANEL = 'panel';
    case RADIO = 'radio';
    case OTHER = 'other';

    public function label(): string
    {
        return match($this) {
            self::RRL => 'РРЛ',
            self::PANEL => 'Панельная антенна',
            self::RADIO => 'Радиоблок',
            self::OTHER => 'Другое',
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

    public function isRrl(): bool
    {
        return $this === self::RRL;
    }

    public function isRadio(): bool
    {
        return $this === self::RADIO;
    }

    public function isOther(): bool
    {
        return $this === self::OTHER;
    }
}
