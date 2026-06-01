<?php

declare(strict_types=1);

namespace App\Service\Calculation\Equipment;

use App\Dto\Calculation\Equipment\Calculate\EquipmentCalculationResult;
use App\Dto\Calculation\Equipment\Calculate\EquipmentCalculator;
use App\Dto\Calculation\Equipment\Calculate\EquipmentSummaryResult;
use App\Dto\Calculation\Equipment\Calculate\RectangleEquipmentForCalculationDto;
use App\Dto\Calculation\Equipment\Calculate\RoundEquipmentForCalculationDto;
use App\Dto\DefaultConstant;
use App\Entity\CalculationEquipment;
use App\Enum\Equipment\EquipmentGroupEnum;
use App\Exception\NotFoundException;
use App\Repository\CalculationEquipmentRepository;
use App\Repository\CalculationRepository;

final readonly class CalculationWindEquipmentService
{
    public function __construct(
        private CalculationRepository $calculationRepository,
        private CalculationEquipmentRepository $calculationEquipmentRepository,
    ) {
    }

    public function calculate(int $calculationId): array
    {
        $calculation = $this->calculationRepository->findById($calculationId);
        $calculationData = $calculation->getCalculationData();
        if (! $calculation || ! $calculationData) {
            throw new NotFoundException(sprintf('Расчет с id %s не найден', $calculationId));
        }

        $windRegion = $calculation->getCalculationData()->getWindRegion();
        if (! $windRegion) {
            throw new NotFoundException('Не указана ветровой район');
        }

        $terrainType = $calculation->getCalculationData()->getTerrainType();
        if (! $terrainType) {
            throw new NotFoundException('Не указан тип местности');
        }

        $calculationEquipments = $this->calculationEquipmentRepository->findByCalculationAndGroups($calculationId, EquipmentGroupEnum::forCalculation());

        $equipmentsDto = $this->buildCalculators($calculationEquipments, $calculationData);

        $result = [];

        foreach ($calculationEquipments as $equipment) {
            $equipmentDto = $equipmentsDto[$equipment->getId()];
            $result[$equipment->getEquipmentGroup()->value][$equipment->getEquipmentType()->value][] = new EquipmentCalculationResult(
                fullName: $equipment->getEquipmentParams()['fullName'],
                quantity: $equipment->getQuantity(),
                monthHeight: $equipment->getMountingHeight(),
                kze: $equipmentDto->getKze(),
                oneEquipmentArea: $equipmentDto->equipment->calcArea(),
                pipeStandArea: 0,
                windPress: $equipmentDto->windRegion->pressureKgPerM(),
                securityCoefficient: $equipmentDto->calculateShading(),
                cxInf: $equipmentDto->equipment->calcCXInf(),
                kLambda: $equipmentDto->equipment->calcKLambda(),
                cxEquipment: $equipmentDto->equipment->calcCX(),
                cxPipeStand: 1.3,
                shadingCoefficient: DefaultConstant::SECURITY_COEFFICIENT,
                pressOnOneEquipment: $equipmentDto->pressOnOneEquipment(),
                heightGroup: $equipment->getEquipmentParams()['heightGroup'],
                operator: $equipment->getOperator(),
                dimensions: $this->dimensionsBuilder($equipment),
            );
        }

        return $result;
    }

    /**
     * @return EquipmentSummaryResult[]
     */
    public function calculateSummary(int $calcId): array
    {
        $calculation = $this->calculationRepository->findById($calcId);
        $calculationData = $calculation->getCalculationData();
        if (! $calculation || ! $calculationData) {
            throw new NotFoundException(sprintf('Расчет с id %s не найден', $calcId));
        }

        $windRegion = $calculationData->getWindRegion();
        if (! $windRegion) {
            throw new NotFoundException('Не указана ветровой район');
        }

        $terrainType = $calculationData->getTerrainType();
        if (! $terrainType) {
            throw new NotFoundException('Не указан тип местности');
        }

        $allGroups = array_map(fn(EquipmentGroupEnum $g) => $g->value, EquipmentGroupEnum::cases());
        $calculationEquipments = $this->calculationEquipmentRepository->findByCalculationAndGroups($calcId, $allGroups);

        $calculators = $this->buildCalculators($calculationEquipments, $calculationData);

        $before = ['area' => 0.0, 'pressure' => 0.0, 'weight' => 0.0];
        $after  = ['area' => 0.0, 'pressure' => 0.0, 'weight' => 0.0];

        foreach ($calculationEquipments as $equipment) {
            $calc = $calculators[$equipment->getId()];
            $group = $equipment->getEquipmentGroup();
            $qty = $equipment->getQuantity();
            $area = $calc->equipment->calcArea() * $qty;
            $pressure = $calc->pressOnOneEquipment() * $qty;
            $weight = (float)($equipment->getEquipmentParams()['weight'] ?? 0) * $qty;

            if ($group === EquipmentGroupEnum::EXIST || $group === EquipmentGroupEnum::DISMANT) {
                $before['area']     += $area;
                $before['pressure'] += $pressure;
                $before['weight']   += $weight;
            }

            if ($group === EquipmentGroupEnum::EXIST || $group === EquipmentGroupEnum::PLAIN) {
                $after['area']     += $area;
                $after['pressure'] += $pressure;
                $after['weight']   += $weight;
            }
        }

        return [
            new EquipmentSummaryResult('До модернизации',    $before['area'], $before['pressure'], $before['weight']),
            new EquipmentSummaryResult('После модернизации', $after['area'],  $after['pressure'],  $after['weight']),
        ];
    }

    /**
     * @param CalculationEquipment[] $equipments
     * @return EquipmentCalculator[]
     */
    private function buildCalculators(array $equipments, mixed $calculationData): array
    {
        $calculators = [];
        foreach ($equipments as $equipment) {
            if ($equipment->getEquipmentType()->isRrl()) {
                $calculators[$equipment->getId()] = new EquipmentCalculator(
                    equipment: new RoundEquipmentForCalculationDto(
                        diameter: $equipment->getEquipmentParams()['diameter'],
                        weight: $equipment->getEquipmentParams()['weight'],
                    ),
                    windRegion: $calculationData->getWindRegion(),
                    terrainTypeEnum: $calculationData->getTerrainType(),
                    mountHeight: $equipment->getMountingHeight(),
                    equipmentTypeEnum: $equipment->getEquipmentType(),
                    quantity: $equipment->getQuantity(),
                );
            } else {
                $calculators[$equipment->getId()] = new EquipmentCalculator(
                    equipment: new RectangleEquipmentForCalculationDto(
                        height: $equipment->getEquipmentParams()['height'],
                        width: $equipment->getEquipmentParams()['width'],
                        depth: $equipment->getEquipmentParams()['depth'],
                        weight: $equipment->getEquipmentParams()['weight'],
                    ),
                    windRegion: $calculationData->getWindRegion(),
                    terrainTypeEnum: $calculationData->getTerrainType(),
                    mountHeight: $equipment->getMountingHeight(),
                    equipmentTypeEnum: $equipment->getEquipmentType(),
                    quantity: $equipment->getQuantity(),
                );
            }
        }

        return $calculators;
    }

    private function dimensionsBuilder(CalculationEquipment $equipment): string
    {
        if ($equipment->getEquipmentType()->isRrl()) {
            return sprintf('(Ø%s мм; %s кг)', $equipment->getEquipmentParams()['diameter'], $equipment->getEquipmentParams()['weight']);
        }

        return sprintf(
            '(%sx%sx%s мм; %s кг)',
            $equipment->getEquipmentParams()['height'],
            $equipment->getEquipmentParams()['width'],
            $equipment->getEquipmentParams()['depth'],
            $equipment->getEquipmentParams()['weight'],
        );
    }
}
