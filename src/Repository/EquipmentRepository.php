<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Equipment;
use App\Enum\Equipment\EquipmentTypeEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class EquipmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Equipment::class);
    }

    public function findByBrandAndModel(string $brand, string $model): ?Equipment
    {
        return $this->findOneBy(['brand' => $brand, 'model' => $model]);
    }

    /**
     * @return Equipment[]
     */
    public function searchByQueryAndType(EquipmentTypeEnum $type, string $query): array
    {
        $qb = $this->createQueryBuilder('e')
            ->andWhere('e.type = :type')
            ->andWhere('e.fullName LIKE :query')
            ->setParameter('type', $type)
            ->setParameter('query', '%' . $query . '%')
            ->getQuery();

        return $qb->getResult();
    }

    public function saveEquipment(Equipment $equipment): void
    {
        $this->getEntityManager()->persist($equipment);
        $this->getEntityManager()->flush();
    }
}
