<?php

declare(strict_types=1);

namespace App\Repository\Gauge;

use App\Entity\Gauge\GaugeRoundSolid;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class GaugeRoundSolidRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GaugeRoundSolid::class);
    }

    public function save(GaugeRoundSolid $entity): void
    {
        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();
    }

    /** @return GaugeRoundSolid[] */
    public function findAllOrderedByDiameter(): array
    {
        return $this->createQueryBuilder('r')
            ->orderBy('r.diameter', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
