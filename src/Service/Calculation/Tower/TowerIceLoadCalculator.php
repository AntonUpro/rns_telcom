<?php

declare(strict_types=1);

namespace App\Service\Calculation\Tower;

use App\Dto\Calculation\Platform\TotalPlatformCalculationDto;
use App\Dto\Calculation\Tower\TowerIceLoadRowDto;
use App\Entity\Calculation;
use App\Enum\CalculationData\TerrainTypeEnum;
use App\Enum\Pillar\PlatformSectionTypeEnum;

/**
 * Гололёдная нагрузка на элементы башни по секциям (СП 20.13330.2016, раздел 12):
 *
 *   i' = b · k · μ2 · ρ · g · γf
 *
 * где k берётся для отметки середины секции.
 */
final readonly class TowerIceLoadCalculator
{
    /** Доля обледеневающей поверхности элемента (п. 12.2) */
    private const MU2 = 0.6;

    /** Плотность льда, г/см³ */
    private const ICE_DENSITY = 0.9;

    /** Ускорение свободного падения, м/с² */
    private const GRAVITY = 9.81;

    /** Коэффициент надёжности по гололёдной нагрузке */
    private const RELIABILITY_COEFFICIENT = 1.8;

    /**
     * Коэффициент k изменения толщины стенки гололёда по высоте
     * (СП 20.13330.2016, таблица 12.2): высота, м => [A, B, C].
     */
    private const HEIGHT_COEFFICIENTS = [
        5   => [0.8, 0.6, 0.4],
        10  => [1.0, 0.8, 0.6],
        20  => [1.2, 1.0, 0.8],
        30  => [1.4, 1.2, 1.0],
        50  => [1.6, 1.4, 1.2],
        70  => [1.8, 1.6, 1.4],
        100 => [2.0, 1.8, 1.6],
        200 => [2.2, 2.0, 1.8],
        300 => [2.4, 2.2, 2.0],
        350 => [2.5, 2.3, 2.2],
    ];

    /**
     * @return TowerIceLoadRowDto[] пусто, если не задан гололёдный район или тип местности
     */
    public function calculate(Calculation $calculation, TotalPlatformCalculationDto $frame): array
    {
        $icingRegion = $calculation->getCalculationData()?->getIcingRegion();
        $terrainType = $calculation->getCalculationData()?->getTerrainType();

        if ($icingRegion === null || $terrainType === null) {
            return [];
        }

        $thickness = $icingRegion->normativeThicknessMm();
        $rows = [];

        foreach ($frame->platformSections as $section) {
            if ($section->type !== PlatformSectionTypeEnum::SECTION) {
                continue;
            }

            $middleMarkM = ($section->mountingHeightSection + $section->heightSection / 2) / 1000;
            $k = $this->heightCoefficient($middleMarkM, $terrainType);

            $rows[] = new TowerIceLoadRowDto(
                sectionNumber: $section->numberSection,
                topMark: ($section->mountingHeightSection + $section->heightSection) / 1000,
                k: $k,
                mu2: self::MU2,
                thickness: $thickness,
                density: self::ICE_DENSITY,
                gravity: self::GRAVITY,
                reliabilityCoefficient: self::RELIABILITY_COEFFICIENT,
                load: $thickness * $k * self::MU2 * self::ICE_DENSITY * self::GRAVITY * self::RELIABILITY_COEFFICIENT,
            );
        }

        return $rows;
    }

    /**
     * Линейная интерполяция по таблице 12.2; ниже 5 м и выше 350 м — крайние значения.
     */
    private function heightCoefficient(float $heightM, TerrainTypeEnum $terrainType): float
    {
        $column = match ($terrainType) {
            TerrainTypeEnum::A => 0,
            TerrainTypeEnum::B => 1,
            TerrainTypeEnum::C => 2,
        };

        $prevHeight = null;
        $prevValue = null;
        foreach (self::HEIGHT_COEFFICIENTS as $height => $values) {
            $value = $values[$column];
            if ($heightM <= $height) {
                if ($prevHeight === null) {
                    return $value;
                }

                return $prevValue + ($value - $prevValue) * ($heightM - $prevHeight) / ($height - $prevHeight);
            }
            $prevHeight = $height;
            $prevValue = $value;
        }

        return $prevValue;
    }
}
