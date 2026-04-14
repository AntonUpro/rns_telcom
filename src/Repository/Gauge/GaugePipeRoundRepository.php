<?php

declare(strict_types=1);

namespace App\Repository\Gauge;

use App\Entity\Gauge\GaugePipeRound;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class GaugePipeRoundRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GaugePipeRound::class);
    }

    public function save(GaugePipeRound $entity): void
    {
        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();
    }

    /** @return GaugePipeRound[] */
    public function findAllOrderedByDiameter(): array
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.outerDiameter', 'ASC')
            ->addOrderBy('p.wallThickness', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
