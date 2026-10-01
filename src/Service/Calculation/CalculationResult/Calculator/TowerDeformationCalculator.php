<?php

declare(strict_types=1);

namespace App\Service\Calculation\CalculationResult\Calculator;

use App\Dto\Calculation\CalculationResult\Row\TowerDeformationRowDto;
use App\Entity\Calculation;

/**
 * Перемещения верхних узлов башни от нормативных нагрузок.
 *
 * Высота сооружения H берётся из исходных данных башни (м),
 * допустимое линейное перемещение = H / 100 (в мм: H · 1000 / 100),
 * КИ = линейное перемещение / допустимое.
 */
final class TowerDeformationCalculator implements TableCalculatorInterface
{
    private const ALLOWABLE_DISPLACEMENT_RATIO = 1 / 100;

    public function calculateRows(array $rawRows, ?Calculation $calculation = null): array
    {
        $height = $calculation?->getCalculationData()?->getTowerSpecificData()?->towerHeight;
        $displacementAllowable = $height > 0 ? $height * 1000 * self::ALLOWABLE_DISPLACEMENT_RATIO : null;

        return array_map(function (array $raw) use ($height, $displacementAllowable): array {
            $row = TowerDeformationRowDto::fromArray($raw);

            $kUse = $row->displacement !== null && $displacementAllowable !== null
                ? round($row->displacement / $displacementAllowable, 4)
                : null;

            return $row->withComputed($height, $displacementAllowable, $kUse)->toArray();
        }, $rawRows);
    }
}
