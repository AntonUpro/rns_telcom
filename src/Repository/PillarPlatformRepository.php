<?php

namespace App\Repository;

use App\Entity\PillarPlatform;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PillarPlatform>
 */
class PillarPlatformRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PillarPlatform::class);
    }

    // Add custom methods here if needed
}