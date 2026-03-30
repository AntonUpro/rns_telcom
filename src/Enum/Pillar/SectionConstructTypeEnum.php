<?php

declare(strict_types=1);

namespace App\Enum\Pillar;

enum SectionConstructTypeEnum: string
{
    case ROUND_PIPE = 'round_pipe';
    case SQUARE_PIPE = 'square_pipe';
    case ANGLE = 'angle';
    case CHANNEL = 'channel';
    case DOUBLE_ANGLE = 'double_angle';
    case OTHER = 'other';

    public function label(): string
    {
        return match ($this) {
            self::ROUND_PIPE => 'Труба круглая',
            self::SQUARE_PIPE => 'Труба квадратная',
            self::ANGLE => 'Уголок',
            self::CHANNEL => 'Швеллер',
            self::DOUBLE_ANGLE => 'Парный уголок',
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

    public function cx(): float
    {
        return match ($this) {
            self::ROUND_PIPE => 1.2,
            self::SQUARE_PIPE,
            self::ANGLE,
            self::CHANNEL,
            self::DOUBLE_ANGLE,
            self::OTHER => 1.4,
        };
    }
}
