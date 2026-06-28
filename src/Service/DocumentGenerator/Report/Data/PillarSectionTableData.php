<?php

declare(strict_types=1);

namespace App\Service\DocumentGenerator\Report\Data;

use App\Enum\Pillar\PillarEnum;

/**
 * Исходные данные сечений стоек для таблицы «Исходные данные сечений стойки».
 *
 * Структура строк (ROWS): ключ — высота от низа стойки в метрах (целое число),
 * значение — [n_pre, Asp_cm2, n_non, As_cm2, sigma_sp_MPa, N_N].
 *
 * Геометрические параметры (D, d, A) вычисляются в buildTableInitialData через PillarEnum.
 * Толщина стенки задана в WALL: [wall_bottom_mm, wall_top_mm] — линейная интерполяция.
 * Материальные константы заданы в MATERIAL: [Rsp, Rs, Rsc, Rb, Eb].
 */
final class PillarSectionTableData
{
    /** @var array<string, array{0: float, 1: float, 2: float, 3: float, 4: float}> */
    private const MATERIAL = [
        // [Rsp, Rs, Rsc, Rb, Eb]
        'СК26.1-1.1' => [1130, 695, 400, 24.00, 36000],
        'СК26.2-1.1' => [1130, 695, 400, 24.00, 36000], // TODO: уточнить
        'СК26.1-6.1' => [1130, 695, 400, 22.00, 36000], // TODO: уточнить
        'СК26.1-3.3' => [1130, 695, 400, 24.00, 36000], // TODO: уточнить
    ];

    /** @var array<string, array{0: float, 1: float}> толщина стенки [низ, верх] в мм */
    private const WALL = [
        'СК26.1-1.1' => [75.0, 55.0],
        'СК26.2-1.1' => [75.0, 55.0], // TODO: уточнить
        'СК26.1-6.1' => [75.0, 55.0], // TODO: уточнить
        'СК26.1-3.3' => [75.0, 55.0], // TODO: уточнить
    ];

    private const AREA_PRESTRESSING_REINFORCEMENT = [
        'СК26.1-1.1' => 1.131,
        'СК26.2-1.1' => 1.131,
        'СК26.1-6.1' => 1.131,
        'СК26.1-3.3' => 1.131,
    ];

    private const AREA_NON_PRESTRESSING_REINFORCEMENT = [
        'СК26.1-1.1' => 1.131,
        'СК26.2-1.1' => 1.131,
        'СК26.1-6.1' => 1.131,
        'СК26.1-3.3' => 1.131,
    ];

    /**
     * Расчетное сопротивление бетона
     */
    private const CALCULATED_CONCRETE_RESISTANCE = [
        'СК26.1-1.1' => 24,
        'СК26.2-1.1' => 24,
        'СК26.1-6.1' => 22,
        'СК26.1-1.5' => 24,
        'СК26.1-3.3' => 24,
    ];

    /**
     * Данные по строкам: ключ — высота от низа стойки (м, целое),
     * значение — [n_пред, n_непред].
     *
     * @var array<string, array<int, array{0:int, 2:int}>>
     */
    private const ROWS = [
        'СК26.1-1.1' => [
            0 => [12, 15],
            1 => [12, 15],
            2 => [12, 15],
            3 => [12, 15],
            4 => [12, 15],
            5 => [12, 15],
            6 => [12, 15],
            7 => [12, 12],
            8 => [12, 12],
            9 => [12, 12],
            10 => [12, 12],
            11 => [12, 9],
            12 => [12, 9],
            13 => [12, 6],
            14 => [12, 6],
            15 => [12, 3],
            16 => [12, 3],
            17 => [12, 2],
            18 => [12, 2],
            19 => [12, 2],
            20 => [12, 2],
            21 => [12, 2],
            22 => [12, 2],
            23 => [12, 2],
            24 => [12, 2],
            25 => [12, 2],
            26 => [12, 2],
        ],

        'СК26.2-1.1' => [
            0 => [20, 6],
            1 => [20, 6],
            2 => [20, 6],
            3 => [20, 6],
            4 => [20, 6],
            5 => [20, 6],
            6 => [20, 6],
            7 => [20, 6],
            8 => [20, 6],
            9 => [20, 6],
            10 => [20, 6],
            11 => [20, 6],
            12 => [20, 6],
            13 => [20, 6],
            14 => [20, 6],
            15 => [20, 6],
            16 => [20, 6],
            17 => [20, 6],
            18 => [20, 4],
            19 => [20, 4],
            20 => [20, 4],
            21 => [20, 4],
            22 => [20, 4],
            23 => [20, 4],
            24 => [20, 4],
            25 => [20, 4],
            26 => [20, 4],
        ],

        'СК26.1-6.1' => [
            0 => [20, 6],
            1 => [20, 6],
            2 => [20, 6],
            3 => [20, 6],
            4 => [20, 6],
            5 => [20, 6],
            6 => [20, 3],
            7 => [20, 3],
            8 => [20, 3],
            9 => [20, 3],
            10 => [20, 2],
            11 => [20, 2],
            12 => [20, 2],
            13 => [20, 2],
            14 => [20, 2],
            15 => [20, 2],
            16 => [20, 2],
            17 => [20, 2],
            18 => [20, 2],
            19 => [20, 2],
            20 => [20, 2],
            21 => [20, 2],
            22 => [20, 2],
            23 => [20, 2],
            24 => [20, 2],
            25 => [20, 2],
            26 => [20, 2],
        ],
        'СК26.1-1.5' => [
            0 => [19, 3],
            1 => [19, 3],
            2 => [19, 3],
            3 => [19, 3],
            4 => [19, 3],
            5 => [19, 3],
            6 => [19, 3],
            7 => [19, 3],
            8 => [19, 3],
            9 => [19, 3],
            10 => [19, 3],
            11 => [19, 3],
            12 => [19, 3],
            13 => [19, 3],
            14 => [19, 2],
            15 => [19, 2],
            16 => [19, 2],
            17 => [19, 2],
            18 => [19, 2],
            19 => [19, 2],
            20 => [19, 2],
            21 => [19, 2],
            22 => [19, 2],
            23 => [19, 2],
            24 => [19, 2],
            25 => [19, 2],
            26 => [19, 2],
        ],

        'СК26.1-3.3' => [
            0 => [14, 3],
            1 => [14, 3],
            2 => [14, 3],
            3 => [14, 3],
            4 => [14, 3],
            5 => [14, 3],
            6 => [14, 3],
            7 => [14, 3],
            8 => [14, 3],
            9 => [14, 3],
            10 => [14, 3],
            11 => [14, 3],
            12 => [14, 3],
            13 => [14, 3],
            14 => [14, 2],
            15 => [14, 2],
            16 => [14, 2],
            17 => [14, 2],
            18 => [14, 2],
            19 => [14, 2],
            20 => [14, 2],
            21 => [14, 2],
            22 => [14, 2],
            23 => [14, 2],
            24 => [14, 2],
            25 => [14, 2],
            26 => [14, 2],
        ],
    ];

    /** @return array{0: float, 1: float, 2: float, 3: float, 4: float} [Rsp, Rs, Rsc, Rb, Eb] */
    public static function getMaterial(PillarEnum $pillar): array
    {
        return self::MATERIAL[$pillar->value]
            ?? throw new \InvalidArgumentException("Нет данных материала для {$pillar->value}");
    }

    /** @return array{0: float, 1: float} [wall_bottom_mm, wall_top_mm] */
    public static function getWall(PillarEnum $pillar): array
    {
        return self::WALL[$pillar->value]
            ?? throw new \InvalidArgumentException("Нет данных стенки для {$pillar->value}");
    }

    /**
     * @return array<int, array{0:int, 1:float, 2:int, 3:float, 4:int, 5:float}>
     *         ключ — высота от низа (м), значение — [n_pre, Asp, n_non, As, σsp, N]
     */
    public static function getRows(PillarEnum $pillar): array
    {
        return self::ROWS[$pillar->value]
            ?? throw new \InvalidArgumentException("Нет данных строк для {$pillar->value}");
    }

    public static function getAreaPrestressingReinforcement(PillarEnum $pillar): float
    {
        return self::AREA_PRESTRESSING_REINFORCEMENT[$pillar->value]
            ?? throw new \InvalidArgumentException("Нет данных для {$pillar->value}");
    }

    public static function getAreaNonPrestressingReinforcement(PillarEnum $pillar): float
    {
        return self::AREA_NON_PRESTRESSING_REINFORCEMENT[$pillar->value]
            ?? throw new \InvalidArgumentException("Нет данных для {$pillar->value}");
    }

    public static function getCalculatedConcreteResistance(PillarEnum $pillar): float
    {
        return self::CALCULATED_CONCRETE_RESISTANCE[$pillar->value]
            ?? throw new \InvalidArgumentException("Нет данных для {$pillar->value}");
    }
}
