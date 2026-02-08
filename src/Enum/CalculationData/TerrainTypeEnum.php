<?php

namespace App\Enum\CalculationData;

enum TerrainTypeEnum: string
{
    case A = 'A';
    case B = 'B';
    case C = 'C';

    public function label(): string
    {
        return match($this) {
            self::A => 'A - Открытые побережья, водоемы',
            self::B => 'B - Полевые, сельские местности',
            self::C => 'C - Городские территории',
        };
    }

    /**
     * @param float $height
     * @return float
     * $height - высота в метрах
     */
    public function roughnessCoefficient(float $height): float
    {
        // Коэффициент k для высоты z по СП 20.13330.2016
        // Нормализуем высоту (не может быть отрицательной)
        $height = max(0.0, $height);

        // Таблица данных из СП 20.13330.2016
        $table = [
            5 => [
                'A' => 0.75,
                'B' => 0.5,
                'C' => 0.4,
            ],
            10 => [
                'A' => 1.0,
                'B' => 0.65,
                'C' => 0.4,
            ],
            20 => [
                'A' => 1.25,
                'B' => 0.85,
                'C' => 0.55,
            ],
            40 => [
                'A' => 1.5,
                'B' => 1.1,
                'C' => 0.8,
            ],
            60 => [
                'A' => 1.7,
                'B' => 1.3,
                'C' => 1.0,
            ],
            80 => [
                'A' => 1.85,
                'B' => 1.45,
                'C' => 1.15,
            ],
            100 => [
                'A' => 2.0,
                'B' => 1.6,
                'C' => 1.25,
            ],
            150 => [
                'A' => 2.25,
                'B' => 1.9,
                'C' => 1.55,
            ],
            200 => [
                'A' => 2.45,
                'B' => 2.1,
                'C' => 1.8,
            ],
            250 => [
                'A' => 2.65,
                'B' => 2.3,
                'C' => 2.0,
            ],
            300 => [
                'A' => 2.75,
                'B' => 2.5,
                'C' => 2.2,
            ],
        ];

        $heights = array_keys($table);
        $minHeight = $heights[0];
        $maxHeight = end($heights);

        // Случай для высот <= 5 м
        if ($height <= $minHeight) {
            return $table[$minHeight][$this->value];
        }

        // Случай для высот >= 300 м
        if ($height >= $maxHeight) {
            return $table[$maxHeight][$this->value];
        }

        // Поиск интервала для интерполяции
        $index = 0;
        while ($index < count($heights) - 1 && $heights[$index + 1] < $height) {
            $index++;
        }

        $h1 = $heights[$index];
        $h2 = $heights[$index + 1];
        $k1 = $table[$h1][$this->value];
        $k2 = $table[$h2][$this->value];

        // Линейная интерполяция
        return $k1 + ($k2 - $k1) * ($height - $h1) / ($h2 - $h1);
    }

    public function description(): string
    {
        return match($this) {
            self::A => 'Открытые побережья морей, озер, водохранилищ, сельские местности при отсутствии препятствий',
            self::B => 'Полевые, степные, лесостепные районы, редкий лес, сельские местности с препятствиями высотой более 10 м',
            self::C => 'Городские территории, лесные массивы, другие местности, равномерно покрытые препятствиями высотой более 25 м',
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

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
