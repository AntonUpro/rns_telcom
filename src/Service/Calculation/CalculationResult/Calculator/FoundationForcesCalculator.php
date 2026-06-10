<?php

declare(strict_types=1);

namespace App\Service\Calculation\CalculationResult\Calculator;

use App\Dto\Calculation\CalculationResult\Row\FoundationForcesDto;

class FoundationForcesCalculator implements TableCalculatorInterface
{
    public function calculateRows(array $rawRows): array
    {
        return array_map(function (array $raw): array {
            $row = FoundationForcesDto::fromArray($raw);

            $kUseStability = null;
            $kUseDeformation = null;

            if ($row->q !== null && $row->qU !== null && $row->qU !== 0) {
                $kUseStability = $row->q / $row->qU;
            }

            if ($row->beta !== null && $row->betaU !== null && $row->betaU !== 0) {
                $kUseDeformation = $row->beta / $row->betaU;
            }

            return $row->withComputed($kUseStability, $kUseDeformation)->toArray();
        }, $rawRows);
    }
}
