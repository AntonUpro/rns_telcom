<?php

declare(strict_types=1);

namespace App\Repository\Gauge;

use App\Entity\Gauge\GaugeAngleEqual;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class GaugeAngleEqualRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GaugeAngleEqual::class);
    }

    /**
     * Сохранить уголок равнополочный.
     * Благодаря cascade: persist на $profile, Doctrine автоматически
     * сделает INSERT в gauge_profile до INSERT в gauge_angle_equal.
     * Оба INSERT выполняются в одной транзакции.
     */
    public function save(GaugeAngleEqual $entity): void
    {
        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();
    }

    /**
     * Найти уголок по обозначению, например 'L50×5'.
     */
    public function findByDesignation(string $designation): ?GaugeAngleEqual
    {
        return $this->createQueryBuilder('a')
            ->join('a.profile', 'p')
            ->andWhere('p.designation = :designation')
            ->setParameter('designation', $designation)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Все уголки, отсортированные по ширине полки (возрастание).
     *
     * @return GaugeAngleEqual[]
     */
    public function findAllOrderedByFlange(): array
    {
        return $this->createQueryBuilder('a')
            ->orderBy('a.flangeWidth', 'ASC')
            ->addOrderBy('a.flangeThickness', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
