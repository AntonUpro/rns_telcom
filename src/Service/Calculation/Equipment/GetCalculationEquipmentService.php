<?php

declare(strict_types=1);

namespace App\Service\Calculation\Equipment;

use App\Dto\Calculation\Equipment\AllEquipmentDto;
use App\Dto\Calculation\Equipment\EquipmentDto;
use App\Dto\Calculation\Equipment\GroupEquipmentDto;
use App\Enum\Equipment\EquipmentGroupEnum;
use App\Enum\Equipment\EquipmentTypeEnum;
use App\Repository\CalculationEquipmentRepository;

final readonly class GetCalculationEquipmentService
{
    public function __construct(
        private CalculationEquipmentRepository $calculationEquipmentRepository,
    ) {
    }

    public function getEquipmentByCalculationId(int $calculationId): AllEquipmentDto
    {
        $equipments = $this->calculationEquipmentRepository->findByCalculationId($calculationId);

        $equipmentDto = [];
        foreach ($equipments as $equipmentItem) {
            $equipmentDto[$equipmentItem->getEquipmentGroup()->value][$equipmentItem->getEquipmentType()->value][] = new EquipmentDto(
                id: $equipmentItem->getId(),
                equipmentId: $equipmentItem->getEquipmentParams()['equipmentId'],
                fullName: $equipmentItem->getEquipmentParams()['fullName'],
                type: $equipmentItem->getEquipmentType(),
                diameter: $equipmentItem->getEquipmentParams()['diameter'],
                height: $equipmentItem->getEquipmentParams()['height'],
                width: $equipmentItem->getEquipmentParams()['width'],
                depth: $equipmentItem->getEquipmentParams()['depth'],
                weight: $equipmentItem->getEquipmentParams()['weight'],
                mountHeight: $equipmentItem->getMountingHeight(),
                heightGroup: $equipmentItem->getEquipmentParams()['heightGroup'] ?? 0,
                quantity: $equipmentItem->getQuantity(),
            );
        }

        return new AllEquipmentDto(
            existEquipment: new GroupEquipmentDto(
                rrl: $equipmentDto[EquipmentGroupEnum::EXIST->value][EquipmentTypeEnum::RRL->value] ?? [],
                panel: $equipmentDto[EquipmentGroupEnum::EXIST->value][EquipmentTypeEnum::PANEL->value] ?? [],
                radio: $equipmentDto[EquipmentGroupEnum::EXIST->value][EquipmentTypeEnum::RADIO->value] ?? [],
                other: $equipmentDto[EquipmentGroupEnum::EXIST->value][EquipmentTypeEnum::OTHER->value] ?? [],
            ),
            plainEquipment: new GroupEquipmentDto(
                rrl: $equipmentDto[EquipmentGroupEnum::PLAIN->value][EquipmentTypeEnum::RRL->value] ?? [],
                panel: $equipmentDto[EquipmentGroupEnum::PLAIN->value][EquipmentTypeEnum::PANEL->value] ?? [],
                radio: $equipmentDto[EquipmentGroupEnum::PLAIN->value][EquipmentTypeEnum::RADIO->value] ?? [],
                other: $equipmentDto[EquipmentGroupEnum::PLAIN->value][EquipmentTypeEnum::OTHER->value] ?? [],
            ),
            dismantledEquipment: new GroupEquipmentDto(
                rrl: $equipmentDto[EquipmentGroupEnum::DISMANT->value][EquipmentTypeEnum::RRL->value] ?? [],
                panel: $equipmentDto[EquipmentGroupEnum::DISMANT->value][EquipmentTypeEnum::PANEL->value] ?? [],
                radio: $equipmentDto[EquipmentGroupEnum::DISMANT->value][EquipmentTypeEnum::RADIO->value] ?? [],
                other: $equipmentDto[EquipmentGroupEnum::DISMANT->value][EquipmentTypeEnum::OTHER->value] ?? [],
            ),
        );
    }
}
