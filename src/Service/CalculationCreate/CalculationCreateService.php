<?php

declare(strict_types=1);

namespace App\Service\CalculationCreate;

use App\Entity\Calculation;
use App\Entity\CalculationData;
use App\Entity\User;
use App\Enum\CalculationStatusEnum;
use App\Enum\CalculationTypeEnum;
use App\Enum\RussianRegions;
use Doctrine\ORM\EntityManagerInterface;

class CalculationCreateService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function createCalculation(CalculationTypeEnum $type, string $objectCode, User $user): Calculation
    {
        $calculation = new Calculation();
        $calculation->setUser($user);
        $calculation->setObjectCode($objectCode);
        $calculation->setType($type);
        $calculation->setStatus(CalculationStatusEnum::DRAFT);

        $calculationData = new CalculationData();
        $calculationData->setCalculation($calculation);

        $parseObjectCode = $this->parseObjectCode($objectCode);
        if (count($parseObjectCode) >= 3) {
            $regionCode = $parseObjectCode[1];
            $calculationData->setRegion(RussianRegions::fromCode($regionCode)?->getName());
            $parseBaseStation = array_slice($parseObjectCode, 2);
            if ($parseBaseStation[count($parseBaseStation) - 1] === 'ОТС') {
                $parseBaseStation = array_slice($parseBaseStation, 0, -1);
            }
            $calculationData->setBaseStationNumber(implode('-', $parseBaseStation));
            $calculationData->setAmsType($type->label());
        }

        $this->entityManager->persist($calculationData);
        $this->entityManager->persist($calculation);
        $this->entityManager->flush();

        return $calculation;
    }

    private function parseObjectCode(string $objectCode): array
    {
        return explode('-', $objectCode);
    }
}
