<?php

declare(strict_types=1);

namespace App\Service\Calculation\CalculationResult;

use App\Enum\Calculation\ResultTableTypeEnum;
use App\Service\Calculation\CalculationResult\Calculator\FoundationForcesCalculator;
use App\Service\Calculation\CalculationResult\Calculator\CrackOpeningCalculator;
use App\Service\Calculation\CalculationResult\Calculator\DeformationCalculator;
use App\Service\Calculation\CalculationResult\Calculator\PillarForcesCalculator;
use App\Service\Calculation\CalculationResult\Calculator\StressCalculator;
use App\Service\Calculation\CalculationResult\Calculator\SuperstructureStabilityCalculator;
use App\Service\Calculation\CalculationResult\Calculator\TableCalculatorInterface;

final class CalculationResultCalculatorService
{
    /** @var array<string, TableCalculatorInterface> */
    private array $calculators;

    public function __construct(
        PillarForcesCalculator $pillarForcesCalculator,
        CrackOpeningCalculator $crackOpeningCalculator,
        StressCalculator $stressCalculator,
        SuperstructureStabilityCalculator $superstructureStabilityCalculator,
        DeformationCalculator $deformationCalculator,
        FoundationForcesCalculator $basePillarForcesCalculator,
    ) {
        $this->calculators = [
            ResultTableTypeEnum::PILLAR_FORCES->value => $pillarForcesCalculator,
            ResultTableTypeEnum::CRACK_OPENING->value => $crackOpeningCalculator,
            ResultTableTypeEnum::BRACE_STRESS->value => $stressCalculator,
            ResultTableTypeEnum::SUPERSTRUCTURE_STRESS->value => $stressCalculator,
            ResultTableTypeEnum::SUPERSTRUCTURE_STABILITY->value => $superstructureStabilityCalculator,
            ResultTableTypeEnum::PLATFORM_FORCES->value => $stressCalculator,
            ResultTableTypeEnum::DEFORMATION->value => $deformationCalculator,
            ResultTableTypeEnum::FOUNDATION->value => $basePillarForcesCalculator,
        ];
    }

    /**
     * Вычисляет computed-поля для всех известных таблиц в payload.
     * Таблицы без калькулятора (base_forces, deformation, foundation) возвращаются без изменений.
     *
     * @param array<string, array{enabled?: bool, rows: array}> $payload
     * @return array<string, array{enabled?: bool, rows: array}>
     */
    public function calculateAll(array $payload): array
    {
        foreach ($this->calculators as $key => $calculator) {
            if (! isset($payload[$key]['rows'])) {
                continue;
            }

            $payload[$key]['rows'] = $calculator->calculateRows($payload[$key]['rows']);
        }

        return $payload;
    }
}
