<?php

declare(strict_types=1);

namespace App\Repository\Gauge;

use App\Entity\Gauge\GaugeProfileType;
use App\Enum\Gauge\GaugeProfileTypeEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class GaugeProfileTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GaugeProfileType::class);
    }

    /** Найти тип профиля по коду enum — нужен при создании нового профиля. */
    public function findByCode(GaugeProfileTypeEnum $code): ?GaugeProfileType
    {
        return $this->findOneBy(['code' => $code]);
    }
}
