<?php

declare(strict_types=1);

namespace App\Service\Calculation\CalculationResult\Calculator;

use App\Dto\Calculation\CalculationResult\Row\StressRowDto;
use App\Entity\Calculation;

final class StressCalculator implements TableCalculatorInterface
{
    public function calculateRows(array $rawRows, ?Calculation $calculation = null): array
    {
        return array_map(function (array $raw): array {
            $row = StressRowDto::fromArray($raw);

            if ($row->area === null || $row->area == 0.0
                || $row->momentResistance === null || $row->momentResistance == 0.0
                || $row->ry === null || $row->ry == 0.0
            ) {
                return $row->toArray();
            }

            // σ (кН/см²) = Nрасч·10 / A + Mрасч·100 / Wy
            // где Nрасч в тс, Mрасч в тс·м, A в см², Wy в см³
            $sigma = ($row->nCalc ?? 0.0) * 100 / $row->area
                + ($row->mCalc ?? 0.0) * 10000 / $row->momentResistance;

            $kUse = round($sigma / $row->ry, 4);

            return $row->withComputed(round($sigma, 4), $kUse)->toArray();
        }, $rawRows);
    }
}
