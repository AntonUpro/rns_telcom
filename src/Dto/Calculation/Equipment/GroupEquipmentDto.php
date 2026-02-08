<?php

declare(strict_types=1);

namespace App\Dto\Calculation\Equipment;

class GroupEquipmentDto
{
    public function __construct(
        /** @var array<EquipmentDto> */
        public array $rrl,
        /** @var array<EquipmentDto> */
        public array $panel,
        /** @var array<EquipmentDto> */
        public array $radio,
        /** @var array<EquipmentDto> */
        public array $other,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            array_map(fn (array $item) => EquipmentDto::createFromArray($item), $data['rrl']),
            array_map(fn (array $item) => EquipmentDto::createFromArray($item), $data['panel']),
            array_map(fn (array $item) => EquipmentDto::createFromArray($item), $data['radio']),
            array_map(fn (array $item) => EquipmentDto::createFromArray($item), $data['other']),
        );
    }

    public function toArray(): array
    {
        return [
            'rrl' => array_map(fn (EquipmentDto $item) => $item->toArray(), $this->rrl),
            'panel' => array_map(fn (EquipmentDto $item) => $item->toArray(), $this->panel),
            'radio' => array_map(fn (EquipmentDto $item) => $item->toArray(), $this->radio),
            'other' => array_map(fn (EquipmentDto $item) => $item->toArray(), $this->other),
        ];
    }
}
