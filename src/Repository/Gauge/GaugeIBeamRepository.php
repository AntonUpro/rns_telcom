<?php

declare(strict_types=1);

namespace App\Repository\Gauge;

use App\Entity\Gauge\GaugeIBeam;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class GaugeIBeamRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GaugeIBeam::class);
    }

    public function save(GaugeIBeam $entity): void
    {
        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();
    }

    /** @return GaugeIBeam[] */
    public function searchByQuery(string $query, int $limit = 15): array
    {
        return $this->createQueryBuilder('b')
            ->join('b.profile', 'p')
            ->where('p.designation LIKE :q OR p.name LIKE :q')
            ->setParameter('q', '%' . $query . '%')
            ->orderBy('p.designation', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /** @return GaugeIBeam[] */
    public function findAllOrderedByHeight(): array
    {
        return $this->createQueryBuilder('b')
            ->orderBy('b.height', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
