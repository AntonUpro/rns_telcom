<?php

declare(strict_types=1);

namespace App\Service\Calculation\Pillar\Calculator;

use App\Dto\Calculation\Pillar\Calculate\SectionDto;
use App\Dto\Calculation\Pillar\PartSectionDto;
use App\Dto\DefaultConstant;
use App\Entity\JsonData\Dto\DefaultValues;
use App\Enum\CalculationData\TerrainTypeEnum;
use App\Enum\CalculationData\WindRegionEnum;

class LadderCalculator
{
    public function __construct(
        private SectionDto $sectionDto,
        private WindRegionEnum $windRegionEnum,
        private TerrainTypeEnum $terrainTypeEnum,
        private DefaultValues $defaultValues,
    ) {
    }

    public function calculate(): ?PartSectionDto
    {
        $ladderBottom = $this->defaultValues->constructionValues['ladderBottom'] ?? DefaultConstant::CONSTRUCTION_VALUE_LADDER_BOTTOM;
        $ladderBottom = $ladderBottom * 1000;
        $ladderArea = $this->defaultValues->constructionValues['ladder'] ?? 0;
        if (! $ladderArea) {
            return null;
        }

        $height = $this->sectionDto->height;
        if ($this->sectionDto->topMark < $ladderBottom) {
            $height = 0;
        } elseif ($this->sectionDto->topMark > $ladderBottom && $this->sectionDto->buttonMark() < $ladderBottom) {
            $height = $this->sectionDto->topMark - $ladderBottom;
        }
        $area = $ladderArea * $height / 1000;

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
