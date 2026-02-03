<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\Equipment\EquipmentTypeEnum;
use App\Repository\EquipmentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use DateTimeImmutable;

#[ORM\Entity(repositoryClass: EquipmentRepository::class)]
#[ORM\Index(name: 'idx_equipment_fulltext', columns: ['full_name'], flags: ['fulltext'])]
#[ORM\UniqueConstraint(name: 'uniq_brand_model', columns: ['brand', 'model'])]
#[ORM\Table(name: 'equipment')]
#[ORM\HasLifecycleCallbacks]
class Equipment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(name: 'brand', length: 255)]
    #[Assert\NotBlank(message: 'Название бренда обязательно')]
    #[Assert\Length(max: 255, maxMessage: 'Название бренда не должно превышать 255 символов')]
    private ?string $brand = null;

    #[ORM\Column(name: 'model', length: 255)]
    #[Assert\NotBlank(message: 'Название модели обязательно')]
    #[Assert\Length(max: 255, maxMessage: 'Название модели не должно превышать 255 символов')]
    private ?string $model = null;

    #[ORM\Column(name: 'full_name', length: 512)]
    #[Assert\NotBlank(message: 'Полное название обязательно')]
    #[Assert\Length(max: 512, maxMessage: 'Полное название не должно превышать 512 символов')]
    private ?string $fullName = null;

    #[ORM\Column(name: 'type', length: 50)]
    #[Assert\NotBlank(message: 'Тип оборудования обязателен')]
    #[Assert\Choice(callback: 'getEquipmentTypes', message: 'Выберите корректный тип оборудования')]
    private ?EquipmentTypeEnum $type = null;

    #[ORM\Column(name: 'has_diameter', type: Types::BOOLEAN, nullable: false)]
    private bool $hasDiameter = false;

    #[ORM\Column(name: 'diameter', type: Types::DECIMAL, precision: 8, scale: 2, nullable: true)]
    #[Assert\Positive(message: 'Диаметр должен быть больше нуля')]
    private ?float $diameter = null;

    #[ORM\Column(name: 'height', type: Types::DECIMAL, precision: 8, scale: 2, nullable: true)]
    #[Assert\Positive(message: 'Высота должна быть больше нуля')]
    private ?float $height = null;

    #[ORM\Column(name: 'width', type: Types::DECIMAL, precision: 8, scale: 2, nullable: true)]
    #[Assert\Positive(message: 'Ширина должна быть больше нуля')]
    private ?float $width = null;

    #[ORM\Column(name: 'depth', type: Types::DECIMAL, precision: 8, scale: 2, nullable: true)]
    #[Assert\Positive(message: 'Глубина должна быть больше нуля')]
    private ?float $depth = null;

    #[ORM\Column(name: 'weight', type: Types::DECIMAL, precision: 8, scale: 2, nullable: true)]
    #[Assert\Positive(message: 'Вес должен быть больше нуля')]
    private ?float $weight = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
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

    public function getBrand(): ?string
    {
        return $this->brand;
    }

    public function setBrand(?string $brand): void
    {
        $this->brand = $brand;
    }

    public function getModel(): ?string
    {
        return $this->model;
    }

    public function setModel(?string $model): void
    {
        $this->model = $model;
    }

    public function getFullName(): ?string
    {
        return $this->fullName;
    }

    public function setFullName(?string $fullName): void
    {
        $this->fullName = $fullName;
    }

    public function getType(): ?EquipmentTypeEnum
    {
        return $this->type;
    }

    public function setType(?EquipmentTypeEnum $type): void
    {
        $this->type = $type;
    }

    public function isHasDiameter(): bool
    {
        return $this->hasDiameter;
    }

    public function setHasDiameter(bool $hasDiameter): void
    {
        $this->hasDiameter = $hasDiameter;
    }

    public function getDiameter(): ?float
    {
        return $this->diameter;
    }

    public function setDiameter(?float $diameter): void
    {
        $this->diameter = $diameter;
    }

    public function getHeight(): ?float
    {
        return $this->height;
    }

    public function setHeight(?float $height): void
    {
        $this->height = $height;
    }

    public function getWidth(): ?float
    {
        return $this->width;
    }

    public function setWidth(?float $width): void
    {
        $this->width = $width;
    }

    public function getDepth(): ?float
    {
        return $this->depth;
    }

    public function setDepth(?float $depth): void
    {
        $this->depth = $depth;
    }

    public function getWeight(): ?float
    {
        return $this->weight;
    }

    public function setWeight(?float $weight): void
    {
        $this->weight = $weight;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?DateTimeImmutable $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    public static function getEquipmentTypes(): array
    {
        return array_values(EquipmentTypeEnum::cases());
    }

}
