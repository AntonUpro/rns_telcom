<?php

declare(strict_types=1);

namespace App\Repository\Gauge;

use App\Entity\Gauge\GaugePipeSquare;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class GaugePipeSquareRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GaugePipeSquare::class);
    }

    public function save(GaugePipeSquare $entity): void
    {
        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();
    }

    /** @return GaugePipeSquare[] */
    public function findAllOrderedBySide(): array
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.outerSide', 'ASC')
            ->addOrderBy('p.wallThickness', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
