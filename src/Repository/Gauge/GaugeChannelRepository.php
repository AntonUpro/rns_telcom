<?php

declare(strict_types=1);

namespace App\Repository\Gauge;

use App\Entity\Gauge\GaugeChannel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class GaugeChannelRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GaugeChannel::class);
    }

    public function save(GaugeChannel $entity): void
    {
        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();
    }

    /** @return GaugeChannel[] */
    public function findAllOrderedByHeight(): array
    {
        return $this->createQueryBuilder('c')
            ->orderBy('c.height', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
