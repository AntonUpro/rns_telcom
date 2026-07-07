<?php

declare(strict_types=1);

namespace App\Enum\Pillar;

use App\Enum\Gauge\GaugeProfileTypeEnum;

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

    public function symbol(): string
    {
        return match ($this) {
            self::ROUND_PIPE   => '○',
            self::SQUARE_PIPE  => '□',
            self::ANGLE        => '∟',
            self::CHANNEL      => '[',
            self::DOUBLE_ANGLE => '∟∟',
            self::OTHER        => '—',
        };
    }

    public function toGaugeProfile(): ?GaugeProfileTypeEnum
    {
        return match ($this) {
            self::ROUND_PIPE   => GaugeProfileTypeEnum::PIPE_ROUND,
            self::SQUARE_PIPE  => GaugeProfileTypeEnum::PIPE_SQUARE,
            self::ANGLE        => GaugeProfileTypeEnum::ANGLE_EQUAL,
            self::CHANNEL      => GaugeProfileTypeEnum::CHANNEL,
            self::DOUBLE_ANGLE => GaugeProfileTypeEnum::ANGLE_EQUAL,
            self::OTHER        => null,
        };
    }
}
