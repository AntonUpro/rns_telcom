<?php

declare(strict_types=1);

namespace App\Enum\Pillar;

enum ElementTypeEnum: string
{
    case BELT       = 'belt';
    case BRACE      = 'brace';
    case SPACER     = 'spacer';
    case STRUT      = 'strut';
    case PIPE_STAND = 'pipe_stand';
    case FENCE      = 'fence';
    case OTHER      = 'other';

    public function label(): string
    {
        return match($this) {
            self::BELT       => 'Пояс',
            self::BRACE      => 'Раскос',
            self::SPACER     => 'Распорка',
            self::STRUT      => 'Шпренгель',
            self::PIPE_STAND => 'Трубостойка',
            self::FENCE      => 'Ограждение',
            self::OTHER      => 'Прочее',
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
