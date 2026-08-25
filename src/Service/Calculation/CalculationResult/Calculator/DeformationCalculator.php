<?php

declare(strict_types=1);

namespace App\Service\Calculation\CalculationResult\Calculator;

use App\Dto\Calculation\CalculationResult\Row\DeformationRowDto;
use App\Entity\Calculation;

final class DeformationCalculator implements TableCalculatorInterface
{
    public function calculateRows(array $rawRows, ?Calculation $calculation = null): array
    {
        return array_map(function (array $raw): array {
            $row = DeformationRowDto::fromArray($raw);
            if ($row->angleMax === null || $row->displacement === null || $row->angleAllowable === null) {
                return $row->toArray();
            }
            $kUse = $row->angleMax / $row->angleAllowable;

            return $row->withComputed($kUse)->toArray();
        }, $rawRows);
    }
}
