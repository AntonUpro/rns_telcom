<?php

declare(strict_types=1);

namespace App\Enum\Pillar;

enum SectionConstructTypeEnum: string
{
    case ROUND_PIPE = 'round_pipe';
    case SQUARE_PIPE = 'square_pipe';
    case ANGLE = 'angle';
    case CHANNEL = 'channel';
    case CHANNEL_NARROW_FLANGE = 'channel_narrow_flange';
    case DOUBLE_ANGLE = 'double_angle';
    case STRIP = 'strip';
    case ROUND = 'round';
    case OTHER = 'other';

    public function label(): string
    {
        return match ($this) {
            self::ROUND_PIPE => 'Труба круглая',
            self::SQUARE_PIPE => 'Труба квадратная',
            self::ANGLE => 'Уголок',
            self::CHANNEL => 'Швеллер широкой полкой',
            self::CHANNEL_NARROW_FLANGE => 'Швеллер узкой полкой',
            self::DOUBLE_ANGLE => 'Парный уголок',
            self::STRIP => 'Полоса',
            self::ROUND => 'Круг',
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
            self::ROUND_PIPE,
            self::ROUND => 1.2,
            self::SQUARE_PIPE,
            self::ANGLE,
            self::CHANNEL,
            self::CHANNEL_NARROW_FLANGE,
            self::DOUBLE_ANGLE,
            self::STRIP,
            self::OTHER => 1.4,
        };
    }

    public function symbol(): string
    {
        return match ($this) {
            self::ROUND_PIPE            => '○',
            self::SQUARE_PIPE           => '□',
            self::ANGLE                 => '∟',
            self::CHANNEL               => '[',
            self::CHANNEL_NARROW_FLANGE => ']',
            self::DOUBLE_ANGLE          => '∟∟',
            self::STRIP                 => '▬',
            self::ROUND                 => '●',
            self::OTHER                 => '—',
        };
    }
}
