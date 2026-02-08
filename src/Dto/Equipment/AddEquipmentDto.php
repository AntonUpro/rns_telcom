<?php

declare(strict_types=1);

namespace App\Dto\Equipment;

use App\Enum\Equipment\EquipmentTypeEnum;
use Symfony\Component\Validator\Constraints as Assert;

class AddEquipmentDto
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(max: 100)]
        public string $brand,

        #[Assert\NotBlank]
        #[Assert\Length(max: 150)]
        public string $model,

        #[Assert\NotBlank]
        #[Assert\Choice(callback: [EquipmentTypeEnum::class, 'choices'])]
        public EquipmentTypeEnum $equipmentType,

        public bool $hasDiameter,

        #[Assert\PositiveOrZero]
        public ?float $diameter,

        #[Assert\PositiveOrZero]
        public ?float $width,

        #[Assert\PositiveOrZero]
        public ?float $height,

        #[Assert\PositiveOrZero]
        public ?float $depth,

        #[Assert\NotBlank]
        #[Assert\Positive]
        public float $weight,
    ) {
    }

    public function buildFullName(): string
    {
        return sprintf('%s %s', mb_strtolower($this->brand), mb_strtolower($this->model));
    }

    public function isRrlEquipment(): bool
    {
        return $this->equipmentType->value === EquipmentTypeEnum::RRL->value;
    }
}
