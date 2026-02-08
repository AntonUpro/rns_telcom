<?php

declare(strict_types=1);

namespace App\Service\Calculation\Equipment;

use App\Dto\Calculation\Equipment\EquipmentDto;
use App\Dto\Calculation\Equipment\AllEquipmentDto;
use App\Entity\Calculation;
use App\Entity\CalculationEquipment;
use App\Enum\Equipment\EquipmentGroupEnum;
use App\Exception\NotFoundException;
use App\Repository\CalculationEquipmentRepository;
use App\Repository\CalculationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Throwable;

class SaveCalculationEquipmentService
{
    public function __construct(
        private CalculationEquipmentRepository $calculationEquipmentRepository,
        private CalculationRepository $calculationRepository,
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger,
    ) {
    }

    public function saveEquipmentForCalculation(AllEquipmentDto $saveEquipmentDto, int $calculationId): void
    {
        try {
            $this->entityManager->beginTransaction();

            $calculation = $this->calculationRepository->findById($calculationId);
            if (! $calculation instanceof Calculation) {
                throw new NotFoundException(sprintf('Не найден расчет c id %s', $calculationId));
            }
            $savingEquipment = $this->calculationEquipmentRepository->findByCalculationId($calculationId);

            $equipmentIdsForSave = [];

            // Существующее оборудование
            /** @var EquipmentDto $equipmentDto */
            foreach ([
                         ...$saveEquipmentDto->existEquipment->rrl,
                         ...$saveEquipmentDto->existEquipment->panel,
                         ...$saveEquipmentDto->existEquipment->radio,
                         ...$saveEquipmentDto->existEquipment->other,
                     ] as $equipmentDto) {
                $equipmentIdsForSave[] = $equipmentDto->id;
                $this->calculationEquipmentRepository->saveOrUpdateEquipment(
                    $equipmentDto,
                    $savingEquipment[$equipmentDto->id] ?? null,
                    EquipmentGroupEnum::EXIST,
                    $calculation,
                );

            }

            // Планируемое оборудование
            /** @var EquipmentDto $equipmentDto */
            foreach ([
                         ...$saveEquipmentDto->plainEquipment->rrl,
                         ...$saveEquipmentDto->plainEquipment->panel,
                         ...$saveEquipmentDto->plainEquipment->radio,
                         ...$saveEquipmentDto->plainEquipment->other,
                     ] as $equipmentDto) {
                $equipmentIdsForSave[] = $equipmentDto->id;
                $this->calculationEquipmentRepository->saveOrUpdateEquipment(
                    $equipmentDto,
                    $savingEquipment[$equipmentDto->id] ?? null,
                    EquipmentGroupEnum::PLAIN,
                    $calculation,
                );
            }

            // Демонтируемое оборудование
            /** @var EquipmentDto $equipmentDto */
            foreach ([
                         ...$saveEquipmentDto->dismantledEquipment->rrl,
                         ...$saveEquipmentDto->dismantledEquipment->panel,
                         ...$saveEquipmentDto->dismantledEquipment->radio,
                         ...$saveEquipmentDto->dismantledEquipment->other,
                     ] as $equipmentDto) {
                $equipmentIdsForSave[] = $equipmentDto->id;
                $this->calculationEquipmentRepository->saveOrUpdateEquipment(
                    $equipmentDto,
                    $savingEquipment[$equipmentDto->id] ?? null,
                    EquipmentGroupEnum::DISMANT,
                    $calculation,
                );
            }

            $savingEquipmentIds = array_keys($savingEquipment);
            $deleteIds = array_diff($savingEquipmentIds, $equipmentIdsForSave);

            $this->calculationEquipmentRepository->deleteByIds($deleteIds);

            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (Throwable $exception) {
            $this->entityManager->rollback();
            $this->logger->error($exception->getMessage());
        }
    }
}
