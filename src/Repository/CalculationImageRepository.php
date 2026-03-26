<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\CalculationImage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CalculationImage>
 */
class CalculationImageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CalculationImage::class);
    }

    /**
     * @return CalculationImage[]
     */
    public function findByCalculation(int $calculationId): array
    {
        return $this->createQueryBuilder('ci')
            ->where('ci.calculation = :calculationId')
            ->setParameter('calculationId', $calculationId)
            ->orderBy('ci.imageType', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByCalculationAndType(int $calculationId, string $imageType): ?CalculationImage
    {
        return $this->createQueryBuilder('ci')
            ->where('ci.calculation = :calculationId')
            ->andWhere('ci.imageType = :imageType')
            ->setParameter('calculationId', $calculationId)
            ->setParameter('imageType', $imageType)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
