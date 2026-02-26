<?php

declare(strict_types=1);

namespace App\Service\Calculation\Pillar\Calculator;

use App\Dto\Calculation\Pillar\Calculate\SectionDto;
use App\Dto\Calculation\Pillar\PartSectionDto;
use App\Dto\DefaultConstant;
use App\Entity\JsonData\Dto\DefaultValues;
use App\Enum\CalculationData\TerrainTypeEnum;
use App\Enum\CalculationData\WindRegionEnum;

class CableChanelCalculator
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
        $cableTrayBottom = $this->defaultValues->constructionValues['cableTrayBottom'] ?? DefaultConstant::CONSTRUCTION_VALUE_CABLE_TRAY_BOTTOM;
        $cableTrayBottom = $cableTrayBottom * 1000;
        $cableTrayArea = $this->defaultValues->constructionValues['cableTray'] ?? 0;
        if (! $cableTrayArea) {
            return null;
        }

        $height = $this->sectionDto->height;
        if ($this->sectionDto->topMark < $cableTrayBottom) {
            $height = 0;
        } elseif ($this->sectionDto->topMark > $cableTrayBottom && $this->sectionDto->buttonMark() < $cableTrayBottom) {
            $height = $this->sectionDto->topMark - $cableTrayBottom;
        }
        $area = $cableTrayArea * $height / 1000;

        $press = $this->windRegionEnum->pressureKgPerM()
            * $this->terrainTypeEnum->roughnessCoefficient(height: $this->sectionDto->middleMark() / 1000)
            * DefaultConstant::SECURITY_COEFFICIENT
            * $area
            * DefaultConstant::CABLE_CANAL_CX_COEFFICIENT;

        return new PartSectionDto(
            area: $area,
            cx: DefaultConstant::CABLE_CANAL_CX_COEFFICIENT,
            press: $press,
        );
    }
}
