<?php

declare(strict_types=1);

namespace App\Dto\Calculation\Equipment;

use App\Enum\Equipment\EquipmentTypeEnum;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class EquipmentDto
{
    public function __construct(
        public ?int $id,
        public int $equipmentId,
        public string $fullName,
        public EquipmentTypeEnum $type,
        public ?float $diameter,
        public ?float $height,
        public ?float $width,
        public ?float $depth,
        public float $weight,
        public float $mountHeight,

        #[Assert\NotBlank(message: 'Необходимо указать количество оборудования')]
        #[Assert\Range(min: 1, max: 300, minMessage: 'Минимальное колчество оборудования 1')]
        public int $quantity,
    ) {
    }

    public static function createFromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            equipmentId: $data['equipmentId'],
            fullName: $data['fullName'],
            type: EquipmentTypeEnum::from($data['type']),
            diameter: $data['diameter'],
            height: $data['height'],
            width: $data['width'],
            depth: $data['depth'],
            weight: $data['weight'],
            mountHeight: $data['mountHeight'],
            quantity: $data['quantity'],
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'equipmentId' => $this->equipmentId,
            'fullName' => $this->fullName,
            'type' => $this->type->value,
            'diameter' => $this->diameter,
            'height' => $this->height,
            'width' => $this->width,
            'depth' => $this->depth,
            'weight' => $this->weight,
            'mountHeight' => $this->mountHeight,
            'quantity' => $this->quantity,
        ];
    }
}
