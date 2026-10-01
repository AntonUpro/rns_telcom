<?php

declare(strict_types=1);

namespace App\Service\Calculation\CalculationResult\Calculator;

use App\Dto\Calculation\CalculationResult\Row\TowerFlangeBoltShearRowDto;
use App\Entity\Calculation;

/**
 * Напряжения во фланцевых болтах башни (срез, одна плоскость среза).
 *
 * Ab (брутто) берётся по диаметру, Rbs — по классу прочности (сопротивление срезу),
 * σ = P / (n · Ab), где P — максимальная нагрузка (тс → Н), Ab — см² → мм²,
 * Кисп = σ / Rbs.
 */
final class TowerFlangeBoltShearCalculator implements TableCalculatorInterface
{
    /** тс → Н */
    private const TF_TO_N = 9.81 * 1000;
    /** см² → мм² */
    private const CM2_TO_MM2 = 100;

    public function calculateRows(array $rawRows, ?Calculation $calculation = null): array
    {
        return array_map(static function (array $raw): array {
            $row = TowerFlangeBoltShearRowDto::fromArray($raw);

            $grossArea = $row->diameter?->grossArea();
            $rbs = $row->strengthClass?->shearResistance();

            $sigma = $grossArea !== null && $row->boltCount > 0 && $row->maxLoad > 0
                ? round($row->maxLoad * self::TF_TO_N / ($row->boltCount * $grossArea * self::CM2_TO_MM2), 2)
                : null;

            $kUse = $sigma !== null && $rbs !== null
                ? round($sigma / $rbs, 4)
                : null;

            return $row->withComputed($grossArea, $sigma, $rbs !== null ? (float)$rbs : null, $kUse)->toArray();
        }, $rawRows);
    }
}
