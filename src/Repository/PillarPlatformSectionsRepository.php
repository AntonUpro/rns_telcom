<?php

namespace App\Repository;

use App\Entity\PillarPlatformSection;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PillarPlatformSection>
 */
class PillarPlatformSectionsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PillarPlatformSection::class);
    }

    public function deleteSectionByNumberAndCalculationId(array $sectionNumbers, int $pillarPlatformId): void
    {
        $this->createQueryBuilder('s')
            ->delete()
            ->where('s.number_section IN (:sectionNumbers)')
            ->andWhere('s.pillar_platform_id = :pillarPlatformId')
            ->setParameter('sectionNumbers', $sectionNumbers)
            ->setParameter('pillarPlatformId', $pillarPlatformId)
            ->getQuery()
            ->execute();
    }
}
