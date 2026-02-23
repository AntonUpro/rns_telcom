<?php

declare(strict_types=1);

namespace App\Service\Calculation\Pillar;

use App\Dto\Calculation\Pillar\Calculate\StrengtheningDto;
use App\Dto\Calculation\Pillar\PartSectionDto;
use App\Dto\Calculation\Pillar\PillarCalculationDto;
use App\Dto\Calculation\Pillar\SectionCollectionDto;
use App\Dto\Calculation\Pillar\TotalCalculationDataDto;
use App\Dto\DefaultConstant;
use App\Entity\CalculationData;
use App\Enum\Pillar\FormConstructEnum;
use App\Exception\NotFoundException;
use App\Repository\CalculationRepository;
use App\Service\Calculation\Pillar\Calculator\CableCalculator;
use App\Service\Calculation\Pillar\Calculator\CableChanelCalculator;
use App\Service\Calculation\Pillar\Calculator\LadderCalculator;
use App\Service\Calculation\Pillar\Calculator\PillarCalculator;

class PillarWindLoadCalculationService
{
    public function __construct(
        private SectionBuilderService $sectionBuilderService,
        private CalculationRepository $calculationRepository,
    ) {
    }

    public function calculate(int $calculationId): SectionCollectionDto
    {
        $calculation = $this->calculationRepository->findById($calculationId);
        if (! $calculation) {
            throw new NotFoundException('Расчет с ID ' . $calculationId . ' не найден');
        }

        $calculationData = $calculation->getCalculationData();
        if (! $calculationData) {
            throw new NotFoundException('Данные расчета с ID ' . $calculationId . ' не найдены');
        }

        $this->validateCalculationData($calculationData);

        $sections = new SectionCollectionDto();
        $sectionCalculations = $this->sectionBuilderService->build(
            pillarEnum: $calculationData->getConcretePillarSpecificData()?->toEnumPillar(),
            height: $calculationData->getAmsHeightMm(),
            markBottom: $calculationData->getConcretePillarSpecificData()->buttomMark ?? 0,
            strengtheningDto: $calculationData->getConcretePillarSpecificData()->strengtheningExist
                ? new StrengtheningDto(
                    height: 100,
                    width: 100,
                    depth: 100,
                    type: FormConstructEnum::SQUARE,
                )
                : null,
        );
        foreach ($sectionCalculations as $sectionCalculation) {
            $pillarCalculator = new PillarCalculator(
                sectionDto: $sectionCalculation,
                windRegionEnum: $calculationData->getWindRegion(),
                terrainTypeEnum: $calculationData->getTerrainType(),
                pillarEnum: $calculationData->getConcretePillarSpecificData()?->toEnumPillar(),
                height: $calculationData->getAmsHeightMm(),
            );

            $cableChanelCalculator = new CableChanelCalculator(
                sectionDto: $sectionCalculation,
                windRegionEnum: $calculationData->getWindRegion(),
                terrainTypeEnum: $calculationData->getTerrainType(),
                areaInMeter: $calculationData->getConcretePillarSpecificData()->areaInMeterCabelChanel ?? 0,
            );

            $ladderCalculator = new LadderCalculator(
                sectionDto: $sectionCalculation,
                windRegionEnum: $calculationData->getWindRegion(),
                terrainTypeEnum: $calculationData->getTerrainType(),
                areaInMeter: $calculationData->getConcretePillarSpecificData()->areaInMeterLadder ?? 0,
            );

            $cableCalculator = new CableCalculator(
                sectionDto: $sectionCalculation,
                windRegionEnum: $calculationData->getWindRegion(),
                terrainTypeEnum: $calculationData->getTerrainType(),
                equipments: $calculationData->getCalculation()->getCalculationEquipments()->toArray(),
            );

            $sections->add(
                new PillarCalculationDto(
                    totalCalculationDataDto: new TotalCalculationDataDto(
                        numberSection: $sectionCalculation->number,
                        heightSection: $sectionCalculation->height,
                        kze: $calculationData->getTerrainType()->roughnessCoefficient($sectionCalculation->middleMark() / 1000),
                        windPress: $calculationData->getWindRegion()->pressureKgPerM(),
                        shadingCoefficient: DefaultConstant::SECURITY_COEFFICIENT,
                        topHeight: $sectionCalculation->topMark,
                    ),
                    pillarPart: $pillarCalculator->calculate(),
                    cableChanelPart: $cableChanelCalculator->calculate(),
                    cablePart: $cableCalculator->calculate(),
                    ladderPart: $ladderCalculator->calculate(),
                )
            );
        }

        return $sections;
    }

    private function validateCalculationData(CalculationData $calculationData): void
    {
        if (! $calculationData->getAmsType()) {
            throw new NotFoundException('Не найдены данные о колонне');
        }

        if (! $calculationData->getWindRegion()) {
            throw new NotFoundException('Не указан ветровой район');
        }

        if (! $calculationData->getTerrainType()) {
            throw new NotFoundException('Не указан тип местности');
        }

        if (! $calculationData->getAmsHeight()) {
            throw new NotFoundException('Не указана высота опоры');
        }

        if (! $calculationData->getConcretePillarSpecificData()?->pillarStamp) {
            throw new NotFoundException('Не указана марка опоры');
        }

        if (count($calculationData->getCalculation()->getCalculationEquipments()) === 0) {
            throw new NotFoundException('Не указано оборудование на опоре');
        }

        $calculationData->getConcretePillarSpecificData()?->toEnumPillar();

    }
}
