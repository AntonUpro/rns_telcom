<?php
// src/Entity/Calculation.php

namespace App\Entity;

use App\Enum\CalculationStatusEnum;
use App\Enum\CalculationTypeEnum;
use App\Repository\CalculationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use DateTimeImmutable;

#[ORM\Entity(repositoryClass: CalculationRepository::class)]
#[ORM\Table(name: 'calculations')]
#[ORM\HasLifecycleCallbacks]
class Calculation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'calculations')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $user = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Название расчета обязательно')]
    #[Assert\Length(max: 255, maxMessage: 'Название не должно превышать 255 символов')]
    private ?string $name = null;

    #[ORM\Column(length: 50)]
    #[Assert\Choice(callback: 'getAvailableTypes', message: 'Выберите корректный тип расчета')]
    private ?CalculationTypeEnum $type = null;

    #[ORM\Column(length: 20)]
    #[Assert\Choice(callback: 'getAvailableStatuses', message: 'Выберите корректный статус')]
    private ?CalculationStatusEnum $status = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?DateTimeImmutable $updatedAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?DateTimeImmutable $deletedAt = null;

    #[ORM\OneToOne(mappedBy: 'calculation', targetEntity: CalculationData::class, cascade: ['persist', 'remove'])]
    private ?CalculationData $calculationData = null;

    #[ORM\OneToOne(mappedBy: 'calculation', targetEntity: CalculationResults::class, cascade: ['persist', 'remove'])]
    private ?CalculationResults $calculationResults = null;

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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getType(): ?CalculationTypeEnum
    {
        return $this->type;
    }

    public function setType(CalculationTypeEnum $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getTypeLabel(): string
    {
        return $this->type->label();
    }

    public function getStatus(): ?CalculationStatusEnum
    {
        return $this->status;
    }

    public function setStatus(CalculationStatusEnum $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getStatusLabel(): string
    {
        return $this->status->label();
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
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

    public function getDeletedAt(): ?DateTimeImmutable
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(?DateTimeImmutable $deletedAt): static
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    public function isDeleted(): bool
    {
        return $this->deletedAt !== null;
    }

    public function softDelete(): void
    {
        $this->deletedAt = new DateTimeImmutable();
    }

    public function restore(): void
    {
        $this->deletedAt = null;
    }

    public function getCalculationData(): ?CalculationData
    {
        return $this->calculationData;
    }

    public function setCalculationData(?CalculationData $calculationData): static
    {
        // Устанавливаем обратную связь
        if ($calculationData !== null && $calculationData->getCalculation() !== $this) {
            $calculationData->setCalculation($this);
        }

        $this->calculationData = $calculationData;

        return $this;
    }

    public function getCalculationResults(): ?CalculationResults
    {
        return $this->calculationResults;
    }

    public function setCalculationResults(?CalculationResults $calculationResults): static
    {
        if ($calculationResults !== null && $calculationResults->getCalculation() !== $this) {
            $calculationResults->setCalculation($this);
        }

        $this->calculationResults = $calculationResults;

        return $this;
    }

    public static function getAvailableTypes(): array
    {
        return array_values(CalculationTypeEnum::cases());
    }

    public static function getAvailableStatuses(): array
    {
        return array_values(CalculationStatusEnum::cases());
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'type_label' => $this->getTypeLabel(),
            'status' => $this->status,
            'status_label' => $this->getStatusLabel(),
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
            'user_id' => $this->user?->getId(),
            'user_name' => $this->user?->getFullName(),
        ];
    }
}
