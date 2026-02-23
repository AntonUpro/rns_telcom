<?php

declare(strict_types=1);

namespace App\Service\Calculation\Pillar\Calculator;

use App\Dto\Calculation\Pillar\Calculate\SectionDto;
use App\Dto\Calculation\Pillar\PartSectionDto;
use App\Dto\DefaultConstant;
use App\Enum\CalculationData\TerrainTypeEnum;
use App\Enum\CalculationData\WindRegionEnum;

class LadderCalculator
{
    public function __construct(
        private SectionDto $sectionDto,
        private WindRegionEnum $windRegionEnum,
        private TerrainTypeEnum $terrainTypeEnum,
        private ?float $areaInMeter,
    ) {
    }

    public function calculate(): ?PartSectionDto
    {
        if (! $this->areaInMeter) {
            return null;
        }

        $area = $this->areaInMeter * $this->sectionDto->height / 1000;
        $press = $this->windRegionEnum->pressureKgPerM()
            * $this->terrainTypeEnum->roughnessCoefficient(height: $this->sectionDto->middleMark() / 1000)
            * DefaultConstant::SECURITY_COEFFICIENT
            * $area
            * DefaultConstant::LADDER_CX_COEFFICIENT;

        return new PartSectionDto(
            area: $area,
            cx: DefaultConstant::LADDER_CX_COEFFICIENT,
            press: $press,
        );
    }
}
