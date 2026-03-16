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
}
