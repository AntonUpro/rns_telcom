<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Calculation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Calculation|null find($id, $lockMode = null, $lockVersion = null)
 */
final class CalculationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Calculation::class);
    }

    public function findById($calculationId): ?Calculation
    {
        return $this->find($calculationId);
    }

    public function getUserCalculationStats(): array
    {
        return $this->createQueryBuilder('c')
            ->select('u.id, u.firstName, u.lastName, u.email, COUNT(c.id) AS total')
            ->join('c.user', 'u')
            ->groupBy('u.id, u.firstName, u.lastName, u.email')
            ->orderBy('total', 'DESC')
            ->setMaxResults(20)
            ->getQuery()
            ->getArrayResult();
    }
}
