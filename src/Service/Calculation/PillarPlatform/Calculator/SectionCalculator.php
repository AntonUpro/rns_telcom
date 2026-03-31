<?php

declare(strict_types=1);

namespace App\Service\Calculation\PillarPlatform\Calculator;

use App\Dto\Calculation\PillarPlatform\ElementDto;
use App\Dto\Calculation\PillarPlatform\ElementsCollectionDto;
use App\Dto\Calculation\PillarPlatform\PillarPlatformSectionDto;
use App\Dto\DefaultConstant;
use App\Entity\PillarPlatformSection;
use App\Enum\CalculationData\TerrainTypeEnum;
use App\Enum\CalculationData\WindRegionEnum;
use App\Enum\Pillar\ElementTypeEnum;
use App\Enum\Pillar\PlatformSectionTypeEnum;
use App\Enum\Pillar\SectionConstructTypeEnum;

final readonly class SectionCalculator
{
    public function __construct(
        public WindRegionEnum $windRegion,
        public TerrainTypeEnum $terrainTypeEnum,
        public PillarPlatformSection $section,
    ) {
    }

    public function calculate(): PillarPlatformSectionDto
    {
        $areaContourSection = $this->section->getHeight() / 1000 * ($this->section->getWidthBottom() + $this->section->getWidthTop()) / 2 / 1000;

        $middleHeight = ($this->section->getMountHeightBottom() + $this->section->getMountHeightTop()) / 2 / 1000;

        $kze = $this->terrainTypeEnum->roughnessCoefficient($middleHeight);

        $elements = $this->buildElements();

        $cx = $elements->sumAiCxi() / $areaContourSection;

        $fi = $elements->sumElementArea() / $areaContourSection;

        $nu = SectionPermeabilityCalculator::calculate($fi);

        $ct = $cx * (1 + $nu);

        $press = $areaContourSection * ($ct * $this->windRegion->pressureKgPerM() * $kze) * DefaultConstant::SECURITY_COEFFICIENT;
        return new PillarPlatformSectionDto(
            numberSection: $this->section->getNumberSection(),
            type: PlatformSectionTypeEnum::from($this->section->getTypeSection()),
            heightSection: $this->section->getHeight(),
            mountingHeightSection: $this->section->getMountHeightBottom(),
            areaContourSection: $areaContourSection,
            kze: $kze,
            cx: $cx,
            fi: $fi,
            nu: $nu,
            ct: $ct,
            windPress: $this->windRegion->pressureKgPerM(),
            shadingCoefficient: DefaultConstant::SECURITY_COEFFICIENT,
            press: $press,
            elementsCollectionDto: $elements,
        );
    }

    private function buildElements(): ElementsCollectionDto
    {
        $elements = [];
        foreach ($this->section->getElements() as $element) {
            $elements[] = new ElementDto(
                elementType: ElementTypeEnum::from($element['type']),
                sectionConstructType: SectionConstructTypeEnum::from($element['sectionType']),
                with: $element['widthElement'],
                length: $element['lengthElement'],
                count: $element['countElement'],
            );
        }
        return new ElementsCollectionDto(
            elements: $elements,
        );
    }
}
