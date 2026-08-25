<?php

declare(strict_types=1);

namespace App\Enum\Pillar;

enum ElementTypeEnum: string
{
    case BELT = 'belt';
    case BRACE = 'brace';
    case SPACER = 'spacer';
    case SPRENGEL = 'sprengel';
    case PIPE_STAND = 'pipe_stand';
    case FENCE = 'fence';
    case STRUT = 'strut';
    case PLATFORM = 'platform';
    case SUPERSTRUCTURE = 'superstructure';
    case OTHER = 'other';

    public function label(): string
    {
        return match ($this) {
            self::BELT => 'Пояс',
            self::BRACE => 'Раскос',
            self::SPACER => 'Распорка',
            self::SPRENGEL => 'Шпренгель',
            self::PIPE_STAND => 'Трубостойка',
            self::FENCE => 'Ограждение',
            self::STRUT => 'Подкос',
            self::PLATFORM => 'Площадка',
            self::SUPERSTRUCTURE => 'Надстройка',
            self::OTHER => 'Прочее',
        };
    }

    public static function toOptions(): array
    {
        return array_map(
            fn(self $case) => ['value' => $case->value, 'label' => $case->label()],
            self::cases()
        );
    }
}
