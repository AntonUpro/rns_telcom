<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Calculation;
use App\Entity\CalculationResultTable;
use App\Enum\Calculation\ResultTableTypeEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CalculationResultTable|null find($id, $lockMode = null, $lockVersion = null)
 */
final class CalculationResultTableRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CalculationResultTable::class);
    }

    /** @return CalculationResultTable[] */
    public function findByCalculation(Calculation $calculation): array
    {
        return $this->findBy(['calculation' => $calculation]);
    }

    public function findByCalculationAndType(
        Calculation $calculation,
        ResultTableTypeEnum $tableType,
    ): ?CalculationResultTable {
        return $this->findOneBy([
            'calculation' => $calculation,
            'tableType'   => $tableType,
        ]);
    }

    /** @return array<string, CalculationResultTable> keyed by table_type value */
    public function findAllByCalculationIndexed(Calculation $calculation): array
    {
        $rows = $this->findByCalculation($calculation);

        $indexed = [];
        foreach ($rows as $row) {
            $indexed[$row->getTableType()->value] = $row;
        }

        return $indexed;
    }
}
