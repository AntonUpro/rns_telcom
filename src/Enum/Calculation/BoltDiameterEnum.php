<?php

declare(strict_types=1);

namespace App\Enum\Calculation;

/**
 * Номинальные диаметры болтов (анкерных и фланцевых) с площадями сечения.
 */
enum BoltDiameterEnum: int
{
    case D16 = 16;
    case D18 = 18;
    case D20 = 20;
    case D22 = 22;
    case D24 = 24;
    case D27 = 27;
    case D30 = 30;
    case D36 = 36;
    case D42 = 42;
    case D48 = 48;
    case D56 = 56;
    case D64 = 64;
    case D72 = 72;
    case D90 = 90;
    case D100 = 100;
    case D110 = 110;
    case D125 = 125;
    case D140 = 140;

    /** Площадь сечения нетто Abn, см² */
    public function netArea(): float
    {
        return match ($this) {
            self::D16 => 1.57,
            self::D18 => 1.92,
            self::D20 => 2.45,
            self::D22 => 3.03,
            self::D24 => 3.53,
            self::D27 => 4.59,
            self::D30 => 5.61,
            self::D36 => 8.16,
            self::D42 => 11.2,
            self::D48 => 14.72,
            self::D56 => 20.3,
            self::D64 => 26.76,
            self::D72 => 34.6,
            self::D90 => 55.91,
            self::D100 => 69.95,
            self::D110 => 85.56,
            self::D125 => 111.91,
            self::D140 => 141.81,
        };
    }

    /** Площадь сечения брутто, см² */
    public function grossArea(): float
    {
        return match ($this) {
            self::D16 => 2.01055529856,
            self::D18 => 2.54460904974,
            self::D20 => 3.141492654,
            self::D22 => 3.80120611134,
            self::D24 => 4.52374942176,
            self::D27 => 5.725370361915,
            self::D30 => 7.0683584715,
            self::D36 => 10.17843619896,
            self::D42 => 13.85398260414,
            self::D48 => 18.09499768704,
            self::D56 => 24.62930240736,
            self::D64 => 32.16888477696,
            self::D72 => 40.71374479584,
            self::D90 => 63.6152262435,
            self::D100 => 78.53731635,
            self::D110 => 95.0301527835,
            self::D125 => 122.714556796875,
            self::D140 => 153.933140046,
        };
    }

    public static function toOptions(): array
    {
        return array_map(
            static fn(self $case): array => [
                'value' => $case->value,
                'label' => 'М' . $case->value,
                'netArea' => $case->netArea(),
                'rbt' => array_combine(
                    array_map(static fn(AnchorBoltSteelEnum $steel): string => $steel->value, AnchorBoltSteelEnum::cases()),
                    array_map(static fn(AnchorBoltSteelEnum $steel): int => $steel->designResistance($case), AnchorBoltSteelEnum::cases()),
                ),
            ],
            self::cases(),
        );
    }
}
