<?php

declare(strict_types=1);

namespace App\Service\Calculation\CalculationResult\Calculator;

use App\Dto\Calculation\CalculationResult\Row\TowerFoundationLoadRowDto;
use App\Dto\Calculation\CalculationResult\Row\TowerLoadComparisonRowDto;
use App\Enum\Calculation\FoundationLoadKindEnum;

/**
 * Сравнение расчётных нагрузок на фундаменты башни с проектными.
 *
 * Расчётные значения выбираются из таблицы «Максимальные нагрузки, действующие на фундаменты»:
 *   - прижимающая    — строка с максимальным Rz > 0;
 *   - выдергивающая  — строка с минимальным Rz < 0 (берётся по модулю);
 *   - сдвигающая     — |Rx| той же строки.
 *
 * В отличие от остальных калькуляторов зависит от другой таблицы,
 * поэтому не реализует TableCalculatorInterface.
 */
final class TowerLoadComparisonCalculator
{
    /**
     * @param array<int, array<string, mixed>> $comparisonRows
     * @param array<int, array<string, mixed>> $foundationLoadRows
     * @return array<int, array<string, mixed>>
     */
    public function calculateRows(array $comparisonRows, array $foundationLoadRows): array
    {
        $foundationLoads = array_map(
            static fn(array $raw): TowerFoundationLoadRowDto => TowerFoundationLoadRowDto::fromArray($raw),
            $foundationLoadRows,
        );

        return array_map(function (array $raw) use ($foundationLoads): array {
            $row = TowerLoadComparisonRowDto::fromArray($raw);
            $governing = $this->findGoverningLoad($row->loadKind, $foundationLoads);

            return $row->withComputed(
                $governing?->rz !== null ? abs($governing->rz) : null,
                $governing?->rx !== null ? abs($governing->rx) : null,
            )->toArray();
        }, $comparisonRows);
    }

    /**
     * @param TowerFoundationLoadRowDto[] $foundationLoads
     */
    private function findGoverningLoad(
        FoundationLoadKindEnum $loadKind,
        array $foundationLoads,
    ): ?TowerFoundationLoadRowDto {
        $governing = null;

        foreach ($foundationLoads as $load) {
            if ($load->rz === null) {
                continue;
            }

            $matches = match ($loadKind) {
                FoundationLoadKindEnum::PRESSING => $load->rz > 0 && ($governing === null || $load->rz > $governing->rz),
                FoundationLoadKindEnum::PULLING => $load->rz < 0 && ($governing === null || $load->rz < $governing->rz),
            };

            if ($matches) {
                $governing = $load;
            }
        }

        return $governing;
    }
}
