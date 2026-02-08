<?php

declare(strict_types=1);

namespace App\Dto\Calculation\Equipment;

use App\Enum\Equipment\EquipmentGroupEnum;
use App\Enum\Equipment\EquipmentTypeEnum;

class CalculationEquipmentDto
{
    public function __construct(
        public int $id,
        public int $calculationId,
        public EquipmentGroupEnum $equipmentGroup,
        public EquipmentTypeEnum $equipmentType,
        public ?float $mountingHeight,
        public int $quantity,
        public array $equipmentParams,
    ) {
    }

    public static function createFromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            calculationId: $data['calculationId'],
            equipmentGroup: EquipmentGroupEnum::from($data['equipmentGroup']),
            equipmentType: EquipmentTypeEnum::from($data['equipmentType']),
            mountingHeight: $data['mountingHeight'] ?? null,
            quantity: $data['quantity'],
            equipmentParams: $data['equipmentParams'],
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'calculationId' => $this->calculationId,
            'equipmentGroup' => $this->equipmentGroup->value,
            'equipmentGroupLabel' => $this->equipmentGroup->name,
            'equipmentType' => $this->equipmentType->value,
            'equipmentTypeLabel' => $this->equipmentType->label(),
            'mountingHeight' => $this->mountingHeight,
            'quantity' => $this->quantity,
            'equipmentParams' => $this->equipmentParams,
        ];
    }
}