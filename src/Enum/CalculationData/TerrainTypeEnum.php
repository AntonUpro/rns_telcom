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

    public function shortLabel(): string
    {
        return match($this) {
            self::A => 'Открытая местность',
            self::B => 'Сельская местность',
            self::C => 'Городская застройка',
        };
    }

    public function roughnessCoefficient(float $height): float
    {
        // Коэффициент k для высоты z по СП 20.13330.2016
        return match($this) {
            self::A => 0.75 * ($height / 10) ** 0.2,
            self::B => 0.65 * ($height / 10) ** 0.2,
            self::C => 0.40 * ($height / 10) ** 0.2,
        };
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
