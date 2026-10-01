<?php

declare(strict_types=1);

namespace App\Service\Calculation\CalculationResult;

use App\Entity\Calculation;
use App\Enum\Calculation\ResultTableTypeEnum;
use App\Service\Calculation\CalculationResult\Calculator\FoundationForcesCalculator;
use App\Service\Calculation\CalculationResult\Calculator\CrackOpeningCalculator;
use App\Service\Calculation\CalculationResult\Calculator\DeformationCalculator;
use App\Service\Calculation\CalculationResult\Calculator\PillarForcesCalculator;
use App\Service\Calculation\CalculationResult\Calculator\StressCalculator;
use App\Service\Calculation\CalculationResult\Calculator\SuperstructureStabilityCalculator;
use App\Service\Calculation\CalculationResult\Calculator\TableCalculatorInterface;
use App\Service\Calculation\CalculationResult\Calculator\TowerAnchorBoltCalculator;
use App\Service\Calculation\CalculationResult\Calculator\TowerDeformationCalculator;
use App\Service\Calculation\CalculationResult\Calculator\TowerFlangeBoltCalculator;
use App\Service\Calculation\CalculationResult\Calculator\TowerFlangeBoltShearCalculator;
use App\Service\Calculation\CalculationResult\Calculator\TowerLoadComparisonCalculator;

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
        TowerDeformationCalculator $towerDeformationCalculator,
        TowerAnchorBoltCalculator $towerAnchorBoltCalculator,
        TowerFlangeBoltCalculator $towerFlangeBoltCalculator,
        TowerFlangeBoltShearCalculator $towerFlangeBoltShearCalculator,
        private readonly TowerLoadComparisonCalculator $towerLoadComparisonCalculator,
    ) {
        $this->calculators = [
            ResultTableTypeEnum::PILLAR_FORCES->value => $pillarForcesCalculator,
            ResultTableTypeEnum::CRACK_OPENING->value => $crackOpeningCalculator,
            ResultTableTypeEnum::BRACE_STRESS->value => $stressCalculator,
            ResultTableTypeEnum::SUPERSTRUCTURE_STRESS->value => $stressCalculator,
            ResultTableTypeEnum::SUPERSTRUCTURE_STABILITY_BELT->value => $superstructureStabilityCalculator,
            ResultTableTypeEnum::SUPERSTRUCTURE_STABILITY_BRACE->value => $superstructureStabilityCalculator,
            ResultTableTypeEnum::PLATFORM_FORCES->value => $stressCalculator,
            ResultTableTypeEnum::DEFORMATION->value => $deformationCalculator,
            ResultTableTypeEnum::FOUNDATION->value => $basePillarForcesCalculator,
            ResultTableTypeEnum::TOWER_BELT_STABILITY->value => $superstructureStabilityCalculator,
            ResultTableTypeEnum::TOWER_BRACE_STABILITY->value => $superstructureStabilityCalculator,
            ResultTableTypeEnum::TOWER_SPACER_STABILITY->value => $superstructureStabilityCalculator,
            ResultTableTypeEnum::TOWER_DEFORMATION->value => $towerDeformationCalculator,
            ResultTableTypeEnum::TOWER_ANCHOR_BOLTS->value => $towerAnchorBoltCalculator,
            ResultTableTypeEnum::TOWER_FLANGE_BOLTS->value => $towerFlangeBoltCalculator,
            ResultTableTypeEnum::TOWER_FLANGE_BOLTS_SHEAR->value => $towerFlangeBoltShearCalculator,
        ];
    }

    /**
     * Вычисляет computed-поля для всех известных таблиц в payload.
     * Таблицы без калькулятора (base_forces, deformation, foundation) возвращаются без изменений.
     *
     * @param array<string, array{enabled?: bool, rows: array}> $payload
     * @return array<string, array{enabled?: bool, rows: array}>
     */
    public function calculateAll(array $payload, ?Calculation $calculation = null): array
    {
        foreach ($this->calculators as $key => $calculator) {
            if (! isset($payload[$key]['rows'])) {
                continue;
            }

            $payload[$key]['rows'] = $calculator->calculateRows($payload[$key]['rows'], $calculation);
        }

        // Сравнение с проектными нагрузками строится по таблице нагрузок на фундаменты
        $comparisonKey = ResultTableTypeEnum::TOWER_LOAD_COMPARISON->value;
        if (isset($payload[$comparisonKey]['rows'])) {
            $payload[$comparisonKey]['rows'] = $this->towerLoadComparisonCalculator->calculateRows(
                $payload[$comparisonKey]['rows'],
                $payload[ResultTableTypeEnum::TOWER_FOUNDATION_LOADS->value]['rows'] ?? [],
            );
        }

        return $payload;
    }
}
