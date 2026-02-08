<?php

declare(strict_types=1);

namespace App\Service\Equipment;

use App\Enum\Equipment\EquipmentTypeEnum;
use App\Repository\EquipmentRepository;

class SearchEquipmentService
{
    public function __construct(
        private EquipmentRepository $equipmentRepository
    ) {
    }

    /**
     * @param string $query
     * @return array
     */
    public function search(string $query, EquipmentTypeEnum $type): array
    {
      $searchResult = $this->equipmentRepository->searchByQueryAndType($type, $query);
      $response = [];

      foreach ($searchResult as $result) {
          $response[] = [
              'id' => $result->getId(),
              'fullName' => $result->getBrand() . ' ' . $result->getModel(),
              'type' => $result->getType()->value,
              'weight' => $result->getWeight(),
              'diameter' => $result->getDiameter(),
              'width' => $result->getWidth(),
              'height' => $result->getHeight(),
              'depth' => $result->getDepth(),
          ];
      }

        return $response;
    }
}
