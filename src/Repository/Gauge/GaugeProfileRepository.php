<?php

declare(strict_types=1);

namespace App\Repository\Gauge;

use App\Entity\Gauge\GaugeProfile;
use App\Enum\Gauge\GaugeProfileTypeEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class GaugeProfileRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GaugeProfile::class);
    }

    /**
     * Все профили заданного типа.
     *
     * @return GaugeProfile[]
     */
    public function findByType(GaugeProfileTypeEnum $type): array
    {
        return $this->createQueryBuilder('p')
            ->join('p.type', 't')
            ->andWhere('t.code = :code')
            ->setParameter('code', $type)
            ->orderBy('p.designation', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /** Найти по обозначению внутри типа. Пример: findByTypeAndDesignation(ANGLE_EQUAL, 'L50×5'). */
    public function findByTypeAndDesignation(GaugeProfileTypeEnum $type, string $designation): ?GaugeProfile
    {
        return $this->createQueryBuilder('p')
            ->join('p.type', 't')
            ->andWhere('t.code = :code')
            ->andWhere('p.designation = :designation')
            ->setParameter('code', $type)
            ->setParameter('designation', $designation)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
