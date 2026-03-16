<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\PillarPlatformRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PillarPlatformRepository::class)]
#[ORM\Table(name: 'pillar_platform')]
class PillarPlatform
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Calculation::class)]
    #[ORM\JoinColumn(name: 'calculation_id', referencedColumnName: 'id', nullable: false)]
    private Calculation $calculation;

    #[ORM\Column(type: 'decimal', precision: 8, scale: 2, nullable: true)]
    private ?float $mountingHeight = null;

    #[ORM\Column(type: 'decimal', precision: 8, scale: 2, nullable: true)]
    private ?float $mountingHeightStrut = null;

    #[ORM\Column(type: 'integer')]
    private int $facetsCount;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $updatedAt = null;

    #[ORM\OneToMany(mappedBy: 'pillarPlatform', targetEntity: PillarPlatformSection::class, cascade: ['persist', 'remove'])]
    private array $sections;

    public function __construct()
    {
        $this->createdAt = new DateTimeImmutable();
        $this->sections = [];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCalculation(): Calculation
    {
        return $this->calculation;
    }

    public function setCalculation(Calculation $calculation): static
    {
        $this->calculation = $calculation;

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

    public function getMountingHeightStrut(): ?float
    {
        return $this->mountingHeightStrut;
    }

    public function setMountingHeightStrut(?float $mountingHeightStrut): static
    {
        $this->mountingHeightStrut = $mountingHeightStrut;

        return $this;
    }

    public function getFacetsCount(): int
    {
        return $this->facetsCount;
    }

    public function setFacetsCount(int $facetsCount): static
    {
        $this->facetsCount = $facetsCount;

        return $this;
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

    /**
     * @return PillarPlatformSection[]
     */
    public function getSections(): array
    {
        return $this->sections;
    }

    public function addSection(PillarPlatformSection $section): static
    {
        if (!$this->sections->contains($section)) {
            $this->sections[] = $section;
            $section->setPillarPlatform($this);
        }

        return $this;
    }

    public function removeSection(PillarPlatformSection $section): static
    {
        if ($this->sections->contains($section)) {
            $this->sections->removeElement($section);
            // Set the owning side to null (unless already changed)
            if ($section->getPillarPlatform() === $this) {
                $section->setPillarPlatform(null);
            }
        }

        return $this;
    }
}
