<?php

declare(strict_types=1);

namespace App\Service\Equipment;

use App\Dto\Equipment\AddEquipmentDto;
use App\Entity\Equipment;
use App\Repository\EquipmentRepository;
use Exception;

class AddEquipmentService
{
    public function __construct(
        private EquipmentRepository $equipmentRepository,
    ) {
    }

    public function addEquipment(AddEquipmentDto $addEquipmentDto): void
    {
        $equipment = $this->equipmentRepository->findByBrandAndModel($addEquipmentDto->brand, $addEquipmentDto->model);
        if ($equipment) {
            throw new Exception(sprintf('Такая модель оборудования уже существует (Бренд %s, Модель %s))', $addEquipmentDto->brand, $addEquipmentDto->model));
        }

        $equipment = new Equipment();
        $equipment->setBrand($addEquipmentDto->brand);
        $equipment->setModel($addEquipmentDto->model);
        $equipment->setFullName($addEquipmentDto->buildFullName());
        $equipment->setType($addEquipmentDto->equipmentType);

        if ($addEquipmentDto->isRrlEquipment()) {
            $equipment->setHasDiameter(true);
            if (!$addEquipmentDto->diameter) {
                throw new Exception('Диаметр не указан');
            }

            $equipment->setDiameter($addEquipmentDto->diameter);
        } else {
            $equipment->setHeight($addEquipmentDto->height);
            $equipment->setWidth($addEquipmentDto->width);
            $equipment->setDepth($addEquipmentDto->depth);
        }

        $equipment->setWeight($addEquipmentDto->weight);

        $this->equipmentRepository->saveEquipment($equipment);
    }
}
