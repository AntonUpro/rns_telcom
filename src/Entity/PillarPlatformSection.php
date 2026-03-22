<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\PillarPlatformSectionsRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PillarPlatformSectionsRepository::class)]
#[ORM\Table(name: 'pillar_platform_sections')]
class PillarPlatformSection
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: PillarPlatform::class, inversedBy: 'sections')]
    #[ORM\JoinColumn(name: 'pillar_platform_id', referencedColumnName: 'id', nullable: false)]
    private ?PillarPlatform $pillarPlatform = null;

    #[ORM\Column(type: 'string', length: 20)]
    private ?string $typeSection = null;

    #[ORM\Column(type: 'integer')]
    private ?int $numberSection = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 8, scale: 3, nullable: true)]
    private ?int $height = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 8, scale: 3, nullable: true)]
    private ?int $widthBottom = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 8, scale: 3, nullable: true)]
    private ?int $widthTop = null;

    #[ORM\Column(type: 'integer')]
    private ?int $mountHeightBottom = null;

    #[ORM\Column(type: 'integer')]
    private ?int $mountHeightTop = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $elements = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPillarPlatform(): ?PillarPlatform
    {
        return $this->pillarPlatform;
    }

    public function setPillarPlatform(?PillarPlatform $pillarPlatform): static
    {
        $this->pillarPlatform = $pillarPlatform;

        return $this;
    }

    public function getTypeSection(): ?string
    {
        return $this->typeSection;
    }

    public function setTypeSection(string $typeSection): static
    {
        $this->typeSection = $typeSection;

        return $this;
    }

    public function getNumberSection(): ?int
    {
        return $this->numberSection;
    }

    public function setNumberSection(int $numberSection): static
    {
        $this->numberSection = $numberSection;

        return $this;
    }

    public function getHeight(): ?int
    {
        return $this->height;
    }

    public function setHeight(?int $height): static
    {
        $this->height = $height;

        return $this;
    }

    public function getWidthBottom(): ?int
    {
        return $this->widthBottom;
    }

    public function setWidthBottom(?int $widthBottom): static
    {
        $this->widthBottom = $widthBottom;

        return $this;
    }

    public function getWidthTop(): ?int
    {
        return $this->widthTop;
    }

    public function setWidthTop(?int $widthTop): static
    {
        $this->widthTop = $widthTop;

        return $this;
    }

    public function getMountHeightBottom(): ?int
    {
        return $this->mountHeightBottom;
    }

    public function setMountHeightBottom(int $mountHeightBottom): static
    {
        $this->mountHeightBottom = $mountHeightBottom;

        return $this;
    }

    public function getMountHeightTop(): ?int
    {
        return $this->mountHeightTop;
    }

    public function setMountHeightTop(int $mountHeightTop): static
    {
        $this->mountHeightTop = $mountHeightTop;

        return $this;
    }

    public function getElements(): ?array
    {
        return $this->elements;
    }

    public function setElements(?array $elements): static
    {
        $this->elements = $elements;

        return $this;
    }

    public function getCreatedAt(): ?DateTimeImmutable
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
}
