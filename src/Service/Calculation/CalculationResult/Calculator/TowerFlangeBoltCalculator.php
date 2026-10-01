<?php

declare(strict_types=1);

namespace App\Service\Calculation\CalculationResult\Calculator;

use App\Dto\Calculation\CalculationResult\Row\TowerFlangeBoltRowDto;
use App\Entity\Calculation;

/**
 * Напряжения во фланцевых болтах башни (растяжение).
 *
 * Abn берётся по диаметру, Rbp — по классу прочности (сопротивление растяжению),
 * σ = P / (n · Abn), где P — максимальная нагрузка (тс → Н), Abn — см² → мм²,
 * Кисп = σ / Rbp.
 */
final class TowerFlangeBoltCalculator implements TableCalculatorInterface
{
    /** тс → Н */
    private const TF_TO_N = 9.81 * 1000;
    /** см² → мм² */
    private const CM2_TO_MM2 = 100;

    public function calculateRows(array $rawRows, ?Calculation $calculation = null): array
    {
        return array_map(static function (array $raw): array {
            $row = TowerFlangeBoltRowDto::fromArray($raw);

            $netArea = $row->diameter?->netArea();
            $rbp = $row->strengthClass?->tensionResistance();

            $sigma = $netArea !== null && $row->boltCount > 0 && $row->maxLoad > 0
                ? round($row->maxLoad * self::TF_TO_N / ($row->boltCount * $netArea * self::CM2_TO_MM2), 2)
                : null;

            $kUse = $sigma !== null && $rbp !== null
                ? round($sigma / $rbp, 4)
                : null;

            return $row->withComputed($netArea, $sigma, $rbp !== null ? (float)$rbp : null, $kUse)->toArray();
        }, $rawRows);
    }
}
