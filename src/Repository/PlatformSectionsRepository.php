<?php

namespace App\Repository;

use App\Entity\Platform;
use App\Entity\PlatformSection;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PlatformSection>
 */
class PlatformSectionsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PlatformSection::class);
    }

    public function deleteSectionByNumberAndCalculationId(array $sectionNumbers, Platform $platform): void
    {
        $this->createQueryBuilder('s')
            ->delete()
            ->where('s.numberSection IN (:sectionNumbers)')
            ->andWhere('s.platform = :platformId')
            ->setParameter('sectionNumbers', $sectionNumbers)
            ->setParameter('platformId', $platform)
            ->getQuery()
            ->execute();
    }

    /**
     * @return PlatformSection[]
     */
    public function getSectionsByPlatformId(Platform $platform): array
    {
        return $this->createQueryBuilder('s')
            ->select('s')
            ->andWhere('s.platform = :platformId')
            ->setParameter('platformId', $platform)
            ->orderBy('s.numberSection', 'ASC')
            ->getQuery()
            ->execute();
    }
}
