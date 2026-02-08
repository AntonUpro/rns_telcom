<?php

declare(strict_types=1);

namespace App\Dto\Calculation\Equipment;

class AllEquipmentDto
{
    public function __construct(
        public GroupEquipmentDto $existEquipment,
        public GroupEquipmentDto $plainEquipment,
        public GroupEquipmentDto $dismantledEquipment,
    ) {
    }

    public static function create(array $data): self
    {
        return new self(
            GroupEquipmentDto::fromArray($data['existEquipment'] ?? []),
            GroupEquipmentDto::fromArray($data['plainEquipment'] ?? []),
            GroupEquipmentDto::fromArray($data['dismantledEquipment'] ?? []),
        );
    }

    public function toArray(): array
    {
        return [
            'existEquipment' => $this->existEquipment->toArray(),
            'plainEquipment' => $this->plainEquipment->toArray(),
            'dismantledEquipment' => $this->dismantledEquipment->toArray(),
        ];
    }
}
