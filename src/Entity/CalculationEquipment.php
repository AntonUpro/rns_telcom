<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\Equipment\EquipmentGroupEnum;
use App\Enum\Equipment\EquipmentTypeEnum;
use App\Repository\CalculationEquipmentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use DateTimeImmutable;

#[ORM\Entity(repositoryClass: CalculationEquipmentRepository::class)]
#[ORM\Table(name: 'calculation_equipment')]
#[ORM\Index(name: 'idx_calculation_equipment_calculation_id', columns: ['calculation_id'])]
#[ORM\HasLifecycleCallbacks]
class CalculationEquipment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Calculation::class, inversedBy: 'calculationEquipments')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Assert\NotBlank(message: 'Номер расчета обязателен')]
    private ?Calculation $calculation = null;

    #[ORM\Column(name: 'equipment_group', length: 20)]
    #[Assert\NotBlank(message: 'Группа оборудования обязательна')]
    #[Assert\Choice(callback: 'getEquipmentGroups', message: 'Выберите корректную группу оборудования')]
    private ?EquipmentGroupEnum $equipmentGroup = null;

    #[ORM\Column(name: 'equipment_type', length: 20)]
    #[Assert\NotBlank(message: 'Тип оборудования обязателен')]
    #[Assert\Choice(callback: 'getEquipmentTypes', message: 'Выберите корректный тип оборудования')]
    private ?EquipmentTypeEnum $equipmentType = null;

    #[ORM\Column(name: 'mounting_height', type: Types::DECIMAL, precision: 8, scale: 2, nullable: true)]
    #[Assert\PositiveOrZero(message: 'Отметка подвеса должна быть положительной или равной нулю')]
    private ?float $mountingHeight = null;

    #[ORM\Column(name: 'quantity', type: Types::INTEGER)]
    #[Assert\NotBlank(message: 'Количество оборудования обязательно')]
    #[Assert\Positive(message: 'Количество должно быть больше нуля')]
    private int $quantity = 1;

    #[ORM\Column(name: 'equipment_params', type: Types::JSON)]
    #[Assert\NotBlank(message: 'Параметры оборудования обязательны')]
    private array $equipmentParams = [];

    #[ORM\Column(name: 'created_at', type: Types::DATETIME_IMMUTABLE)]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'updated_at', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCalculation(): ?Calculation
    {
        return $this->calculation;
    }

    public function setCalculation(?Calculation $calculation): static
    {
        $this->calculation = $calculation;

        return $this;
    }

    public function getEquipmentGroup(): ?EquipmentGroupEnum
    {
        return $this->equipmentGroup;
    }

    public function setEquipmentGroup(?EquipmentGroupEnum $equipmentGroup): static
    {
        $this->equipmentGroup = $equipmentGroup;

        return $this;
    }

    public function getEquipmentType(): ?EquipmentTypeEnum
    {
        return $this->equipmentType;
    }

    public function setEquipmentType(?EquipmentTypeEnum $equipmentType): static
    {
        $this->equipmentType = $equipmentType;

        return $this;
    }

    public function getMountingHeight(): ?float
    {
        return $this->mountingHeight;
    }

    public function setMountingHeight(?float $mountingHeight): static
    {
        $this->mountingHeight = $mountingHeight;

        return $this;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): static
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getEquipmentParams(): array
    {
        return $this->equipmentParams;
    }

    public function setEquipmentParams(array $equipmentParams): static
    {
        $this->equipmentParams = $equipmentParams;

        return $this;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public static function getEquipmentGroups(): array
    {
        return array_values(EquipmentGroupEnum::cases());
    }

    public static function getEquipmentTypes(): array
    {
        return array_values(EquipmentTypeEnum::cases());
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'calculation_id' => $this->calculation?->getId(),
            'equipment_group' => $this->equipmentGroup?->value,
            'equipment_group_label' => $this->equipmentGroup?->name,
            'equipment_type' => $this->equipmentType?->value,
            'equipment_type_label' => $this->equipmentType?->label(),
            'mounting_height' => $this->mountingHeight,
            'quantity' => $this->quantity,
            'equipment_params' => $this->equipmentParams,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}