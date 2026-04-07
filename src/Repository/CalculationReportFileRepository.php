<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\CalculationReportFile;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CalculationReportFile>
 */
class CalculationReportFileRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CalculationReportFile::class);
    }

    /**
     * @return CalculationReportFile[]
     */
    public function findByCalculation(int $calculationId): array
    {
        return $this->createQueryBuilder('rf')
            ->where('rf.calculation = :calculationId')
            ->setParameter('calculationId', $calculationId)
            ->orderBy('rf.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByCalculationAndType(int $calculationId, string $type): ?CalculationReportFile
    {
        return $this->createQueryBuilder('rf')
            ->where('rf.calculation = :calculationId')
            ->andWhere('rf.type = :type')
            ->setParameter('calculationId', $calculationId)
            ->setParameter('type', $type)
            ->orderBy('rf.version', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
