<?php
// src/Entity/CalculationResults.php

namespace App\Entity;

use App\Repository\CalculationResultsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CalculationResultsRepository::class)]
#[ORM\Table(name: 'calculation_results')]
#[ORM\HasLifecycleCallbacks]
class CalculationResults
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'calculationResults', targetEntity: Calculation::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Calculation $calculation = null;

    #[ORM\Column(type: Types::JSON)]
    private array $results = [];

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notes = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $calculatedAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->calculatedAt = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCalculation(): ?Calculation
    {
        return $this->calculation;
    }

    public function setCalculation(Calculation $calculation): static
    {
        $this->calculation = $calculation;

        return $this;
    }

    public function getResults(): array
    {
        return $this->results;
    }

    public function setResults(array $results): static
    {
        $this->results = $results;

        return $this;
    }

    public function getResultValue(string $key, $default = null)
    {
        return $this->results[$key] ?? $default;
    }

    public function setResultValue(string $key, $value): static
    {
        $this->results[$key] = $value;

        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): static
    {
        $this->notes = $notes;

        return $this;
    }

    public function getCalculatedAt(): \DateTimeImmutable
    {
        return $this->calculatedAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'calculation_id' => $this->calculation?->getId(),
            'results' => $this->results,
            'notes' => $this->notes,
            'calculated_at' => $this->calculatedAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
