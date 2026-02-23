<?php

declare(strict_types=1);

namespace App\Service\Calculation\Equipment;

use App\Dto\Calculation\Equipment\Calculate\EquipmentCalculationResult;
use App\Dto\Calculation\Equipment\Calculate\EquipmentCalculator;
use App\Dto\Calculation\Equipment\Calculate\RectangleEquipmentForCalculationDto;
use App\Dto\Calculation\Equipment\Calculate\RoundEquipmentForCalculationDto;
use App\Dto\DefaultConstant;
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

        $equipmentsDto = [];
        foreach ($calculationEquipments as $equipment) {
            if ($equipment->getEquipmentType()->isRrl()) {
                $equipmentsDto[$equipment->getId()] = new EquipmentCalculator(
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
                continue;
            }

            $equipmentsDto[$equipment->getId()] = new EquipmentCalculator(
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

        $result = [];

        foreach ($calculationEquipments as $equipment) {
            $equipmentDto = $equipmentsDto[$equipment->getId()];
            $result[$equipment->getEquipmentGroup()->value][$equipment->getEquipmentType()->value][] = (new EquipmentCalculationResult(
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
            ))->toArray();
        }

        return $result;
    }
}
