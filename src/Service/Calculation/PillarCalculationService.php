<?php

declare(strict_types=1);

namespace App\Service\Calculation;

use App\Dto\Calculation\ClimateDataDto;
use App\Dto\Calculation\PillarCalculationDataDto;
use App\Dto\Calculation\PillarDataDto;
use App\Dto\Calculation\TotalDataDto;
use App\Entity\CalculationData;
use App\Entity\JsonData\ConcretePillarSpecificData;
use App\Enum\CalculationData\IcingRegionEnum;
use App\Enum\CalculationData\SnowRegionEnum;
use App\Enum\CalculationData\TerrainTypeEnum;
use App\Enum\CalculationData\WindRegionEnum;
use App\Exception\NotFoundCalculationDataException;
use App\Exception\NotFoundException;
use App\Repository\CalculationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Translation\Exception\NotFoundResourceException;
use Throwable;
use DateTime;

final readonly class PillarCalculationService
{
    public function __construct(
        private CalculationRepository $calculationRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function saveGeneralData(PillarCalculationDataDto $calculationDataDto): void
    {
        $calculation = $this->calculationRepository->findById($calculationDataDto->calculationId);
        if (! $calculation) {
            throw new NotFoundResourceException(sprintf('Расчет с id %s не найден', $calculationDataDto->calculationId));
        }

        $calculationData = $calculation->getCalculationData();
        if (! $calculationData) {
            $calculationData = new CalculationData();
            $calculationData->setCalculation($calculation);
        }

        try {
            $this->entityManager->beginTransaction();

            $calculationData->setObjectCode($calculationDataDto->totalData->objectCode);
            $calculationData->setBaseStationNumber($calculationDataDto->totalData->stationNumber);
            $calculationData->setRegion($calculationDataDto->totalData->region);
            $calculationData->setLocality($calculationDataDto->totalData->locality);
            $calculationData->setCustomer($calculationDataDto->totalData->customer);
            $calculationData->setAmsType($calculationDataDto->totalData->amsType);
            $calculationData->setAmsHeight((string)$calculationDataDto->totalData->amsHeight);
            $calculationData->setSurveyDate($calculationDataDto->totalData->inspectionDate
                ? new DateTime($calculationDataDto->totalData->inspectionDate)
                : null);
            $calculationData->setLatitude((string)$calculationDataDto->totalData->latitude);
            $calculationData->setLongitude((string)$calculationDataDto->totalData->longitude);

            $calculationData->setWindRegion(WindRegionEnum::tryFrom($calculationDataDto->climateData->windRegion));
            $calculationData->setTerrainType(TerrainTypeEnum::tryFrom($calculationDataDto->climateData->terrainType));
            $calculationData->setSnowRegion(SnowRegionEnum::tryFrom($calculationDataDto->climateData->snowRegion));
            $calculationData->setIcingRegion(IcingRegionEnum::tryFrom($calculationDataDto->climateData->iceRegion));
            $calculationData->setSpecificDataObject((new ConcretePillarSpecificData(
                $calculationDataDto->pillarData->pillarStamp,
                $calculationDataDto->pillarData->strengtheningExist,
            )));

            $this->entityManager->persist($calculationData);
            $this->entityManager->persist($calculation);
            $this->entityManager->flush();

            $this->entityManager->commit();
        } catch (Throwable $e) {
            $this->entityManager->rollback();
            throw new \Exception(sprintf('Ошибка при сохранении данных расчета: %s', $e->getMessage()));
        }
    }

    public function getCalculationInfo(int $calculationId): PillarCalculationDataDto
    {
        $calculation = $this->calculationRepository->findById($calculationId);

        if (! $calculation) {
            throw new NotFoundException(sprintf('Расчет с id %s не найден', $calculationId));
        }

        if (! $calculation->getCalculationData()) {
            throw new NotFoundCalculationDataException(sprintf('Данные расчета с id %s не найдены', $calculationId));
        }

        return new PillarCalculationDataDto(
            calculationId: $calculationId,
            totalData: new TotalDataDto(
                objectCode: $calculation->getCalculationData()->getObjectCode(),
                stationNumber: $calculation->getCalculationData()->getBaseStationNumber(),
                region: $calculation->getCalculationData()->getRegion(),
                locality: $calculation->getCalculationData()->getLocality(),
                customer: $calculation->getCalculationData()->getCustomer(),
                amsType: $calculation->getCalculationData()->getAmsType(),
                amsHeight: (float)$calculation->getCalculationData()->getAmsHeight(),
                inspectionDate: $calculation->getCalculationData()->getSurveyDate()?->format('Y-m-d'),
                latitude: (float)$calculation->getCalculationData()->getLatitude(),
                longitude: (float)$calculation->getCalculationData()->getLongitude(),
            ),
            pillarData: new PillarDataDto(
                pillarStamp: $calculation->getCalculationData()->getConcretePillarSpecificData()?->pillarStamp,
                strengtheningExist: $calculation->getCalculationData()->getConcretePillarSpecificData()?->strengtheningExist,
            ),
            climateData: new ClimateDataDto(
                windRegion: $calculation->getCalculationData()->getWindRegion()?->value,
                terrainType: $calculation->getCalculationData()->getTerrainType()?->value,
                snowRegion: $calculation->getCalculationData()->getSnowRegion()?->value,
                iceRegion: $calculation->getCalculationData()->getIcingRegion()?->value,
            ),
        );
    }
}
