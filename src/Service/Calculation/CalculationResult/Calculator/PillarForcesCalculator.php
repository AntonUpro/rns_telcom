<?php

declare(strict_types=1);

namespace App\Service\Calculation\CalculationResult\Calculator;

use App\Dto\Calculation\CalculationResult\Row\PillarForcesRowDto;
use App\Entity\Calculation;
use App\Enum\Pillar\PillarEnum;
use App\Service\Calculation\PillarByHeight\SimpleCalculator;
use App\Service\DocumentGenerator\Report\Data\PillarSectionTableData;
use InvalidArgumentException;
use ValueError;

final class PillarForcesCalculator implements TableCalculatorInterface
{
    public function calculateRows(array $rawRows, ?Calculation $calculation = null): array
    {
        $rows = array_map(static fn(array $raw): PillarForcesRowDto => PillarForcesRowDto::fromArray($raw), $rawRows);

        $momentsByMark = $this->calculateMomentsByMark($rows, $calculation);

        return array_map(function (PillarForcesRowDto $row) use ($momentsByMark): array {
            if ($row->pillarType === '') {
                return $row->toArray();
            }

            try {
                $pillar = PillarEnum::from($row->pillarType);
            } catch (ValueError) {
                return $row->toArray();
            }

            if ($row->mAllowableManual) {
                $mAllowable = $row->mAllowable ?? $pillar->getAllowableMomentByStrength();
                $kMax = ($row->mCalc !== null && $mAllowable > 0) ? round($row->mCalc / $mAllowable, 2) : null;

                return $row->withComputed($mAllowable, $kMax, $row->sectionDataAvailable)->toArray();
            }

            $sectionDataAvailable = $this->isSectionDataAvailable($pillar);
            $markKey = $row->mark !== null ? (int)$row->mark * 1000 : null;

            $mAllowable = ($sectionDataAvailable && $markKey !== null && isset($momentsByMark[$markKey]))
                ? $momentsByMark[$markKey]
                : $pillar->getAllowableMomentByStrength();

            $kMax = ($row->mCalc !== null && $mAllowable > 0) ? round($row->mCalc / $mAllowable, 2) : null;

            return $row->withComputed($mAllowable, $kMax, $sectionDataAvailable)->toArray();
        }, $rows);
    }

    /**
     * @param PillarForcesRowDto[] $rows
     * @return array<int, float> отметка (м) => допустимый момент, посчитанный по высоте
     */
    private function calculateMomentsByMark(array $rows, ?Calculation $calculation): array
    {
        $moments = [];
        foreach ($rows as $row) {
            if ($row->mCalc !== null && $row->mark !== null) {
                $markMm = (int)$row->mark * 1000;
                $moments[$markMm] = $row->mCalc;
            }
        }

        return (new SimpleCalculator())->calculateAllowableMomentsByHeight($calculation, $moments);
    }

    private function isSectionDataAvailable(PillarEnum $pillar): bool
    {
        try {
            PillarSectionTableData::getRows($pillar);

            return true;
        } catch (InvalidArgumentException) {
            return false;
        }
    }
}
