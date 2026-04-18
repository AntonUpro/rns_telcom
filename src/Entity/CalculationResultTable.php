<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\Calculation\ResultTableTypeEnum;
use App\Repository\CalculationResultTableRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CalculationResultTableRepository::class)]
#[ORM\Table(name: 'calculation_result_table')]
#[ORM\UniqueConstraint(name: 'uq_calc_result_table', columns: ['calculation_id', 'table_type'])]
#[ORM\HasLifecycleCallbacks]
class CalculationResultTable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::BIGINT)]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Calculation::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Calculation $calculation;

    #[ORM\Column(name: 'table_type', length: 32, enumType: ResultTableTypeEnum::class)]
    private ResultTableTypeEnum $tableType;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $enabled = true;

    #[ORM\Column(type: Types::JSON)]
    private array $rows = [];

    #[ORM\Column(name: 'updated_at', type: Types::DATETIME_IMMUTABLE)]
    private DateTimeImmutable $updatedAt;

    public function __construct(Calculation $calculation, ResultTableTypeEnum $tableType)
    {
        $this->calculation = $calculation;
        $this->tableType   = $tableType;
        $this->updatedAt   = new DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCalculation(): Calculation
    {
        return $this->calculation;
    }

    public function getTableType(): ResultTableTypeEnum
    {
        return $this->tableType;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): static
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function getRows(): array
    {
        return $this->rows;
    }

    public function setRows(array $rows): static
    {
        $this->rows = $rows;

        return $this;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function toArray(): array
    {
        return [
            'id'         => $this->id,
            'table_type' => $this->tableType->value,
            'label'      => $this->tableType->label(),
            'enabled'    => $this->enabled,
            'rows'       => $this->rows,
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }
}
