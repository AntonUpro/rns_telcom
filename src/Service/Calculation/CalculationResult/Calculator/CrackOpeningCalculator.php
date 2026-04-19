<?php

declare(strict_types=1);

namespace App\Service\Calculation\CalculationResult\Calculator;

use App\Dto\Calculation\CalculationResult\Row\CrackOpeningRowDto;

final class CrackOpeningCalculator implements TableCalculatorInterface
{
    public function calculateRows(array $rawRows): array
    {
        return array_map(function (array $raw): array {
            $row = CrackOpeningRowDto::fromArray($raw);

            // Расчёт crackWidthCalc по СП 63.13330 — TODO: реализовать формулу.
            // Пока значение может быть передано фронтом или программным ПК.
            $crackWidthCalc = $row->crackWidthCalc;

            $kMax = ($crackWidthCalc !== null && $row->crackWidthAllowable > 0)
                ? round($crackWidthCalc / $row->crackWidthAllowable, 4)
                : null;

            return $row->withComputed($crackWidthCalc, $kMax)->toArray();
        }, $rawRows);
    }
}
