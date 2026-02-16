<?php

declare(strict_types=1);

namespace App\Repository;

use App\Dto\Calculation\Equipment\EquipmentDto;
use App\Entity\Calculation;
use App\Entity\CalculationEquipment;
use App\Enum\Equipment\EquipmentGroupEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CalculationEquipment|null find($id, $lockMode = null, $lockVersion = null)
 * @method CalculationEquipment|null findOneBy(array $criteria, array $orderBy = null)
 * @method CalculationEquipment[]    findAll()
 * @method CalculationEquipment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CalculationEquipmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CalculationEquipment::class);
    }

    /**
     * @return CalculationEquipment[]
     */
    public function findByCalculationId(int $calculationId): array
    {
        $res = $this->createQueryBuilder('ce')
            ->andWhere('ce.calculation = :calculationId')
            ->setParameter('calculationId', $calculationId)
            ->orderBy('ce.id', 'ASC')
            ->getQuery()
            ->getResult();

        $response = [];
        /** @var CalculationEquipment $item */
        foreach ($res as $item) {
            $response[$item->getId()] = $item;
        }

        return $response;
    }

    /**
     * @param string[] $equipmentGroups
     * @return CalculationEquipment[]
     */
    public function findByCalculationAndGroups(int $calculationId, array $equipmentGroups): array
    {
        return $this->createQueryBuilder('ce')
            ->andWhere('ce.calculation = :calculationId')
            ->andWhere('ce.equipmentGroup IN (:equipmentGroup)')
            ->setParameter('calculationId', $calculationId)
            ->setParameter('equipmentGroup', $equipmentGroups, ArrayParameterType::STRING)
            ->orderBy('ce.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param int[] $ids
     */
    public function deleteByIds(array $ids): void
    {
        $this->createQueryBuilder('ce')
            ->delete()
            ->andWhere('ce.id IN (:calculationId)')
            ->setParameter('calculationId', $ids, ArrayParameterType::INTEGER)
            ->getQuery()
            ->execute();
    }

    /**
     * @return int[]
     */
    public function getEquipmentIdsByCalculationId(int $calculationId): array
    {
        return $this->createQueryBuilder('ce')
            ->select('id')
            ->andWhere('ce.calculation = :calculationId')
            ->setParameter('calculationId', $calculationId)
            ->getQuery()
            ->getSingleColumnResult();
    }

    public function saveOrUpdateEquipment(EquipmentDto $equipmentDto, ?CalculationEquipment $equipment, EquipmentGroupEnum $groupEnum, Calculation $calculation): void
    {
        if (!$equipment instanceof CalculationEquipment) {
            $equipment = new CalculationEquipment();
            $equipment->setCalculation($calculation);
        }

        if ($equipmentDto->quantity <= 0) {
            throw new \Exception('Не указано количество оборудования у ' . $equipmentDto->fullName);
        }

        $equipment->setEquipmentGroup($groupEnum)
            ->setEquipmentType($equipmentDto->type)
            ->setQuantity($equipmentDto->quantity)
            ->setMountingHeight($equipmentDto->mountHeight)
            ->setEquipmentParams(
                [
                    'equipmentId' => $equipmentDto->equipmentId,
                    'type' => $equipmentDto->type,
                    'diameter' => $equipmentDto->diameter,
                    'height' => $equipmentDto->height,
                    'width' => $equipmentDto->width,
                    'depth' => $equipmentDto->depth,
                    'weight' => $equipmentDto->weight,
                    'fullName' => $equipmentDto->fullName,
                    'heightGroup' => $equipmentDto->heightGroup,
                ]
            )
            ->setUpdatedAtValue();
        $this->getEntityManager()->persist($equipment);
    }
}
