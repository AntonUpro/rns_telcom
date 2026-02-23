<?php

declare(strict_types=1);

namespace App\Enum\Pillar;

enum PillarEnum: string
{
    // СК26.1 series
    case CK26_1_1_1 = 'СК26.1-1.1';
    case CK26_1_1_2 = 'СК26.1-1.2';
    case CK26_1_1_5 = 'СК26.1-1.5';
    case CK26_1_1_4 = 'СК26.1-1.4';
    case CK26_1_1_3 = 'СК26.1-1.3';
    case CK26_1_2_1 = 'СК26.1-2.1';
    case CK26_1_2_2 = 'СК26.1-2.2';
    case CK26_1_2_5 = 'СК26.1-2.5';
    case CK26_1_2_4 = 'СК26.1-2.4';
    case CK26_1_2_3 = 'СК26.1-2.3';
    case CK26_1_3_1 = 'СК26.1-3.1';
    case CK26_1_3_4 = 'СК26.1-3.4';
    case CK26_1_3_3 = 'СК26.1-3.3';
    case CK26_1_4_1 = 'СК26.1-4.1';
    case CK26_1_5_1 = 'СК26.1-5.1';
    case CK26_1_6_1 = 'СК26.1-6.1';
    case CK26_1_6_2 = 'СК26.1-6.2';
    case CK26_1_1_0 = 'СК26.1-1.0';
    case CK26_1_2_0 = 'СК26.1-2.0';
    case CK26_1_3_0 = 'СК26.1-3.0';
    case CK26_1_4_0 = 'СК26.1-4.0';
    case CK26_1_6_0 = 'СК26.1-6.0';

    // СК22.1 series
    case CK22_1_1_1 = 'СК22.1-1.1';
    case CK22_1_1_2 = 'СК22.1-1.2';
    case CK22_1_2_1 = 'СК22.1-2.1';
    case CK22_1_2_2 = 'СК22.1-2.2';
    case CK22_1_3_1 = 'СК22.1-3.1';
    case CK22_1_3_2 = 'СК22.1-3.2';
    case CK22_1_1_0 = 'СК22.1-1.0';
    case CK22_1_2_0 = 'СК22.1-2.0';
    case CK22_1_3_0 = 'СК22.1-3.0';

    // СК26.2 series
    case CK26_2_1_1 = 'СК26.2-1.1';
    case CK26_2_1_2 = 'СК26.2-1.2';
    case CK26_2_1_0 = 'СК26.2-1.0';

    // СК22.2 series
    case CK22_2_1_1 = 'СК22.2-1.1';
    case CK22_2_1_2 = 'СК22.2-1.2';
    case CK22_2_1_0 = 'СК22.2-1.0';

    // СК22.3 series
    case CK22_3_1_1 = 'СК22.3-1.1';
    case CK22_3_1_2 = 'СК22.3-1.2';
    case CK22_3_1_0 = 'СК22.3-1.0';

    /**
     * Предельный момент по прочности (kN·m)
     */
    public function getAllowableMomentByStrength(): float
    {
        return match($this) {
            self::CK26_1_1_1 => 47.12,
            self::CK26_1_1_2 => 47.47,
            self::CK26_1_1_5 => 41.75,
            self::CK26_1_1_4 => 43.11,
            self::CK26_1_1_3 => 46.77,
            self::CK26_1_2_1 => 47.12,
            self::CK26_1_2_2 => 47.47,
            self::CK26_1_2_5 => 41.75,
            self::CK26_1_2_4 => 43.11,
            self::CK26_1_2_3 => 46.77,
            self::CK26_1_3_1 => 54.19,
            self::CK26_1_3_4 => 52.68,
            self::CK26_1_3_3 => 53.37,
            self::CK26_1_4_1 => 43.14,
            self::CK26_1_5_1 => 58.91,
            self::CK26_1_6_1 => 46.21,
            self::CK26_1_6_2 => 44.57,
            self::CK26_1_1_0 => 46.67,
            self::CK26_1_2_0 => 46.67,
            self::CK26_1_3_0 => 55.09,
            self::CK26_1_4_0 => 42.36,
            self::CK26_1_6_0 => 46.48,

            self::CK22_1_1_1 => 27.00,
            self::CK22_1_1_2 => 27.55,
            self::CK22_1_2_1 => 33.24,
            self::CK22_1_2_2 => 33.59,
            self::CK22_1_3_1 => 21.95,
            self::CK22_1_3_2 => 21.63,
            self::CK22_1_1_0 => 27.22,
            self::CK22_1_2_0 => 34.59,
            self::CK22_1_3_0 => 21.65,

            self::CK26_2_1_1 => 46.12,
            self::CK26_2_1_2 => 43.92,
            self::CK26_2_1_0 => 46.37,

            self::CK22_2_1_1 => 53.54,
            self::CK22_2_1_2 => 51.21,
            self::CK22_2_1_0 => 53.24,

            self::CK22_3_1_1 => 30.22,
            self::CK22_3_1_2 => 29.97,
            self::CK22_3_1_0 => 31.37,
        };
    }

    /**
     * Предельный момент по образованию трещин (kN·m)
     */
    public function getMomentByCrackFormation(): float
    {
        return match($this) {
            self::CK26_1_1_1 => 13.34,
            self::CK26_1_1_2 => 15.72,
            self::CK26_1_1_5 => 21.51,
            self::CK26_1_1_4 => 11.03,
            self::CK26_1_1_3 => 16.71,
            self::CK26_1_2_1 => 13.34,
            self::CK26_1_2_2 => 15.72,
            self::CK26_1_2_5 => 21.51,
            self::CK26_1_2_4 => 11.03,
            self::CK26_1_2_3 => 16.71,
            self::CK26_1_3_1 => 12.78,
            self::CK26_1_3_4 => 15.39,
            self::CK26_1_3_3 => 16.02,
            self::CK26_1_4_1 => 17.36,
            self::CK26_1_5_1 => 12.39,
            self::CK26_1_6_1 => 19.25,
            self::CK26_1_6_2 => 21.46,
            self::CK26_1_1_0 => 10.80,
            self::CK26_1_2_0 => 10.80,
            self::CK26_1_3_0 => 10.20,
            self::CK26_1_4_0 => 15.60,
            self::CK26_1_6_0 => 15.24,

            self::CK22_1_1_1 => 11.33,
            self::CK22_1_1_2 => 12.35,
            self::CK22_1_2_1 => 11.07,
            self::CK22_1_2_2 => 13.97,
            self::CK22_1_3_1 => 10.12,
            self::CK22_1_3_2 => 9.52,
            self::CK22_1_1_0 => 9.13,
            self::CK22_1_2_0 => 8.80,
            self::CK22_1_3_0 => 9.54,

            self::CK26_2_1_1 => 19.24,
            self::CK26_2_1_2 => 16.22,
            self::CK26_2_1_0 => 15.21,

            self::CK22_2_1_1 => 20.39,
            self::CK22_2_1_2 => 21.19,
            self::CK22_2_1_0 => 20.30,

            self::CK22_3_1_1 => 9.46,
            self::CK22_3_1_2 => 9.43,
            self::CK22_3_1_0 => 9.25,
        };
    }

    /**
     * Геометрические размеры стойки
     */
    public function getGeometricDimensions(): array
    {
        $designation = $this->value;

        // Determine height based on designation prefix
        $height = str_starts_with($designation, 'СК26') ? 26000 : 22600;

        // Bottom diameter is the same for all pillars
        $bottomDiameter = 650;

        // Top diameter depends on series
        $topDiameter = str_starts_with($designation, 'СК26') ? 410 : 440;

        return [
            'height' => $height,
            'bottom_diameter' => $bottomDiameter,
            'top_diameter' => $topDiameter
        ];
    }

    /**
     * Высота стойки
     */
    public function getHeight(): int
    {
        return $this->getGeometricDimensions()['height'];
    }

    /**
     * Нижний диаметр
     */
    public function getBottomDiameter(): int
    {
        return $this->getGeometricDimensions()['bottom_diameter'];
    }

    /**
     * Верхний диаметр
     */
    public function getTopDiameter(): int
    {
        return $this->getGeometricDimensions()['top_diameter'];
    }

    /**
     * Диаметр на заданной высоте методом линейной интерполяции
     * @param float $heightAtPoint Высота от низа в миллиметрах (0 до высоты стойки)
     * @return float Диаметр на заданной высоте в миллиметрах
     * @throws \InvalidArgumentException Если высота вне допустимого диапазона
     */
    public function getDiameterAtHeight(float $heightAtPoint): float
    {
        $dimensions = $this->getGeometricDimensions();
        $totalHeight = $dimensions['height'];
        $bottomDiameter = $dimensions['bottom_diameter'];
        $topDiameter = $dimensions['top_diameter'];

        // Проверка диапазона высоты
        if ($heightAtPoint < 0 || $heightAtPoint > $totalHeight) {
            throw new \InvalidArgumentException(
                "Высота должна быть между 0 и {$totalHeight} мм, получено {$heightAtPoint} мм"
            );
        }

        // Формула линейной интерполяции: d = d_низ + (d_верх - d_низ) * (h / H)
        $diameterDifference = $topDiameter - $bottomDiameter;
        $heightRatio = $heightAtPoint / $totalHeight;

        return $bottomDiameter + ($diameterDifference * $heightRatio);
    }

    public function calcLambdaE(float $height): float
    {
        return 2 * ($height / (($this->getDiameterAtHeight($this->getHeight() - $height) + $this->getTopDiameter()) / 2));
    }

    public static function choices(): array
    {
        $choices = [];
        foreach (self::cases() as $case) {
            $choices[$case->value] = $case->value;
        }

        return $choices;
    }
}
