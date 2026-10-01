<?php

declare(strict_types=1);

namespace App\Enum\Calculation;

/**
 * Марки стали анкерных болтов.
 */
enum AnchorBoltSteelEnum: string
{
    /** Нормативное сопротивление < 290 Н/мм² */
    case ST3 = 'st3';
    /** Нормативное сопротивление 290 … < 390 Н/мм² */
    case S09G2S = '09g2s';

    public function label(): string
    {
        return match ($this) {
            self::ST3 => 'Ст3',
            self::S09G2S => '09Г2С',
        };
    }

    /**
     * Расчётное сопротивление растяжению фундаментных болтов Rbt, Н/мм².
     * Промежуточные диаметры (18, 22, 27) относятся к группе ближайшего большего табличного диаметра.
     */
    public function designResistance(BoltDiameterEnum $diameter): int
    {
        [$st3, $s09g2s] = match (true) {
            $diameter->value <= 20 => [200, 265],
            $diameter->value <= 30 => [190, 245],
            $diameter->value <= 36 => [190, 230],
            $diameter->value <= 56 => [180, 230],
            $diameter->value <= 80 => [180, 220],
            $diameter->value <= 100 => [180, 210],
            default => [165, 210],
        };

        return $this === self::ST3 ? $st3 : $s09g2s;
    }

    public static function toOptions(): array
    {
        return array_map(
            static fn(self $case): array => ['value' => $case->value, 'label' => $case->label()],
            self::cases(),
        );
    }
}
