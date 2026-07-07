<?php

declare(strict_types=1);

namespace App\Service\Calculation\CalculationResult\Calculator;

use App\Dto\Calculation\CalculationResult\Row\SuperstructureStabilityRowDto;

/**
 * Заготовка калькулятора устойчивости элементов надстройки.
 *
 * TODO: реализовать проверку устойчивости сжатых/растянутых элементов
 * надстройки по СП 16.13330.2017 (табл. 32 — предельная гибкость,
 * формулы устойчивости сжатых стержней). Пока — pass-through: строки
 * нормализуются через DTO, sigma/kUse не вычисляются (остаются null).
 */
final class SuperstructureStabilityCalculator implements TableCalculatorInterface
{
    public function calculateRows(array $rawRows): array
    {
        return array_map(
            static fn(array $raw): array => SuperstructureStabilityRowDto::fromArray($raw)->toArray(),
            $rawRows,
        );
    }
}
