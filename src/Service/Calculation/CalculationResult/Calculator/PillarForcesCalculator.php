<?php

declare(strict_types=1);

namespace App\Service\Calculation\CalculationResult\Calculator;

use App\Dto\Calculation\CalculationResult\Row\PillarForcesRowDto;
use App\Enum\Pillar\PillarEnum;
use ValueError;

final class PillarForcesCalculator implements TableCalculatorInterface
{
    public function calculateRows(array $rawRows): array
    {
        return array_map(function (array $raw): array {
            $row = PillarForcesRowDto::fromArray($raw);

            if ($row->pillarType === '') {
                return $row->toArray();
            }

            try {
                $pillar = PillarEnum::from($row->pillarType);
                $mAllowable = $pillar->getAllowableMomentByStrength();
                $kMax = ($row->mCalc !== null && $mAllowable > 0)
                    ? round($row->mCalc / $mAllowable, 2)
                    : null;

                return $row->withComputed($mAllowable, $kMax)->toArray();
            } catch (ValueError) {
                return $row->toArray();
            }
        }, $rawRows);
    }
}
