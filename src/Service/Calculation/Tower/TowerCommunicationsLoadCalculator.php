<?php

declare(strict_types=1);

namespace App\Service\Calculation\Tower;

use App\Dto\Calculation\Pillar\Calculate\SectionDto;
use App\Dto\Calculation\Platform\TotalPlatformCalculationDto;
use App\Dto\Calculation\Tower\TowerCommunicationsSectionDto;
use App\Dto\DefaultConstant;
use App\Entity\Calculation;
use App\Enum\Pillar\PlatformSectionTypeEnum;
use App\Enum\Pillar\FormConstructEnum;
use App\Service\Calculation\Pillar\Calculator\CableCalculator;
use App\Service\Calculation\Pillar\Calculator\CableChanelCalculator;
use App\Service\Calculation\Pillar\Calculator\LadderCalculator;

/**
 * Ветровая нагрузка на коммуникации башни (кабельная трасса, кабельрост, лестница)
 * по секциям каркаса.
 */
final readonly class TowerCommunicationsLoadCalculator
{
    /**
     * @return array<int, TowerCommunicationsSectionDto> ключ — номер секции;
     *         пусто, если для расчёта не заданы значения по умолчанию (диаметры кабелей и т.п.)
     */
    public function calculate(Calculation $calculation, TotalPlatformCalculationDto $frame): array
    {
        $calculationData = $calculation->getCalculationData();
        $defaultValues = $calculationData?->getTowerSpecificData()?->defaultValues;
        $windRegion = $calculationData?->getWindRegion();
        $terrainType = $calculationData?->getTerrainType();

        if ($defaultValues === null || $windRegion === null || $terrainType === null) {
            return [];
        }

        $equipments = $calculation->getCalculationEquipments()->toArray();
        $result = [];

        foreach ($frame->platformSections as $section) {
            if ($section->type !== PlatformSectionTypeEnum::SECTION) {
                continue;
            }

            $sectionDto = new SectionDto(
                number: $section->numberSection,
                height: $section->heightSection,
                diameterTop: 0,
                diameterBottom: 0,
                topMark: (float)($section->mountingHeightSection + $section->heightSection), // мм
                formConstruct: FormConstructEnum::SQUARE,
            );

            $result[$section->numberSection] = new TowerCommunicationsSectionDto(
                sectionNumber: $section->numberSection,
                topMark: $sectionDto->topMark,
                kze: $terrainType->roughnessCoefficient(height: $sectionDto->middleMark() / 1000),
                windPress: $windRegion->pressureKgPerM(),
                securityCoefficient: DefaultConstant::SECURITY_COEFFICIENT,
                cable: (new CableCalculator(
                    sectionDto: $sectionDto,
                    windRegionEnum: $windRegion,
                    terrainTypeEnum: $terrainType,
                    equipments: $equipments,
                    defaultValues: $defaultValues,
                ))->calculate(),
                cableChannel: (new CableChanelCalculator(
                    sectionDto: $sectionDto,
                    windRegionEnum: $windRegion,
                    terrainTypeEnum: $terrainType,
                    defaultValues: $defaultValues,
                ))->calculate(),
                ladder: (new LadderCalculator(
                    sectionDto: $sectionDto,
                    windRegionEnum: $windRegion,
                    terrainTypeEnum: $terrainType,
                    defaultValues: $defaultValues,
                ))->calculate(),
            );
        }

        return $result;
    }
}
