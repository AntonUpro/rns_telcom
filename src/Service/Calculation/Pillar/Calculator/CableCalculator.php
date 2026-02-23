<?php

declare(strict_types=1);

namespace App\Service\Calculation\Pillar\Calculator;

use App\Dto\Calculation\Pillar\Calculate\SectionDto;
use App\Dto\Calculation\Pillar\PartSectionDto;
use App\Dto\DefaultConstant;
use App\Entity\CalculationEquipment;
use App\Enum\CalculationData\TerrainTypeEnum;
use App\Enum\CalculationData\WindRegionEnum;

final class CableCalculator
{
    /**
     * @param CalculationEquipment[] $equipments
     */
    public function __construct(
        private SectionDto $sectionDto,
        private WindRegionEnum $windRegionEnum,
        private TerrainTypeEnum $terrainTypeEnum,
        private array $equipments,
    ) {
    }

    public function calculate(): ?PartSectionDto
    {
        $rrlCount = 0;
        $radioCount = 0;
        foreach ($this->equipments as $equipment) {
            if ($equipment->getEquipmentGroup()->isDismant()) {
                continue;
            }

            if ($equipment->getEquipmentType()->isRrl()) {
                $rrlCount++;
            }

            if ($equipment->getEquipmentType()->isRadio()) {
                $radioCount++;
            }
        }

        if ($rrlCount === 0 && $radioCount === 0) {
            return null;
        }

        $widthCable = $rrlCount * DefaultConstant::CABLE_RRL_DIAMETER
            * $radioCount * DefaultConstant::CABLE_RADIO_POWER_DIAMETER
            * $radioCount * DefaultConstant::CABLE_RADIO_OPTIC_DIAMETER;

        $area = $widthCable / 1000 * $this->sectionDto->height;

        return new PartSectionDto(
            area: $area,
            cx: DefaultConstant::CABLE_CX_COEFFICIENT,
            press: $this->windRegionEnum->pressureKgPerM()
                * $this->terrainTypeEnum->roughnessCoefficient(height: $this->sectionDto->middleMark() / 1000)
                * DefaultConstant::SECURITY_COEFFICIENT
                * $area
                * DefaultConstant::LADDER_CX_COEFFICIENT,
        );
    }
}
