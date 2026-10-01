<?php

declare(strict_types=1);

namespace App\Service\Calculation\CalculationResult\Calculator;

use App\Dto\Calculation\CalculationResult\Row\TowerAnchorBoltRowDto;
use App\Entity\Calculation;

/**
 * Напряжения в анкерных болтах башни.
 *
 * Abn и Rbt берутся из справочников по диаметру и марке стали,
 * σ = k0 · P / (n · Abn), где P — максимальная нагрузка (тс → Н), Abn — см² → мм²,
 * Кисп = σ / Rbt.
 */
final class TowerAnchorBoltCalculator implements TableCalculatorInterface
{
    /** тс → Н */
    private const TF_TO_N = 9.81 * 1000;
    /** см² → мм² */
    private const CM2_TO_MM2 = 100;

    public function calculateRows(array $rawRows, ?Calculation $calculation = null): array
    {
        return array_map(static function (array $raw): array {
            $row = TowerAnchorBoltRowDto::fromArray($raw);

            $netArea = $row->diameter?->netArea();
            $rbt = $row->diameter !== null ? $row->steel?->designResistance($row->diameter) : null;

            $sigma = $netArea !== null && $row->boltCount > 0 && $row->maxLoad > 0 && $row->k0 > 0
                ? round($row->k0 * $row->maxLoad * self::TF_TO_N / ($row->boltCount * $netArea * self::CM2_TO_MM2), 2)
                : null;

            $kUse = $sigma !== null && $rbt !== null
                ? round($sigma / $rbt, 4)
                : null;

            return $row->withComputed($netArea, $sigma, $rbt !== null ? (float)$rbt : null, $kUse)->toArray();
        }, $rawRows);
    }
}
