<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\CalculationDocument;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CalculationDocument>
 */
class CalculationDocumentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CalculationDocument::class);
    }

    /**
     * @return CalculationDocument[]
     */
    public function findByCalculation(int $calculationId): array
    {
        return $this->createQueryBuilder('cd')
            ->where('cd.calculation = :calculationId')
            ->setParameter('calculationId', $calculationId)
            ->orderBy('cd.sortOrder', 'ASC')
            ->addOrderBy('cd.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Удаляет все документы расчёта одним запросом.
     */
    public function deleteByCalculation(int $calculationId): void
    {
        $this->createQueryBuilder('cd')
            ->delete()
            ->where('cd.calculation = :calculationId')
            ->setParameter('calculationId', $calculationId)
            ->getQuery()
            ->execute();
    }
}
