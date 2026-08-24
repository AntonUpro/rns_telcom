<?php

namespace App\Repository;

use App\Entity\PillarPlatform;
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

    public function deleteSectionByNumberAndCalculationId(array $sectionNumbers, PillarPlatform $pillarPlatform): void
    {
        $this->createQueryBuilder('s')
            ->delete()
            ->where('s.numberSection IN (:sectionNumbers)')
            ->andWhere('s.pillarPlatform = :pillarPlatformId')
            ->setParameter('sectionNumbers', $sectionNumbers)
            ->setParameter('pillarPlatformId', $pillarPlatform)
            ->getQuery()
            ->execute();
    }

    /**
     * @return PillarPlatformSection[]
     */
    public function getPlatformSectionsByPillarPlatformId(PillarPlatform $pillarPlatform): array
    {
        return $this->createQueryBuilder('s')
            ->select('s')
            ->andWhere('s.pillarPlatform = :pillarPlatformId')
            ->setParameter('pillarPlatformId', $pillarPlatform)
            ->orderBy('s.numberSection', 'ASC')
            ->getQuery()
            ->execute();
    }
}
