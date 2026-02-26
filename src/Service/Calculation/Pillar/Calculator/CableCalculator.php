<?php

declare(strict_types=1);

namespace App\Service\Calculation\Pillar\Calculator;

use App\Dto\Calculation\Pillar\Calculate\SectionDto;
use App\Dto\Calculation\Pillar\PartSectionDto;
use App\Dto\DefaultConstant;
use App\Entity\CalculationEquipment;
use App\Entity\JsonData\Dto\DefaultValues;
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
        private DefaultValues $defaultValues,
    ) {
    }

    public function calculate(): ?PartSectionDto
    {
        $rrlCount = 0;
        $radioCount = 0;
        $otherCount = 0;
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

            if ($equipment->getEquipmentType()->isOther()) {
                $otherCount++;
            }
        }

        if ($rrlCount === 0 && $radioCount === 0 && $otherCount === 0) {
            return null;
        }

        $diameterRrlCable = $this->defaultValues->cableDiameterValues['rrl'] ?? DefaultConstant::CABLE_RRL_DIAMETER;
        $diameterOpticalCable = $this->defaultValues->cableDiameterValues['optical'] ?? DefaultConstant::CABLE_RADIO_OPTIC_DIAMETER;
        $diameterPowerCable = $this->defaultValues->cableDiameterValues['power'] ?? DefaultConstant::CABLE_RADIO_POWER_DIAMETER;
        $diameterOtherEquipmentCable = $this->defaultValues->cableDiameterValues['otherEquipment'] ?? DefaultConstant::CABLE_OTHER_EQUIPMENT_DIAMETER;

        $shadingCoefficient = $this->defaultValues->shadingCoefficients['cableTray'] ?? DefaultConstant::SHADING_COEFFICIENT_CABLE_TRAY;
        // Отметка низа кабельной трассы
        $cableTrayBottom = $this->defaultValues->constructionValues['cableTrayBottom'] ?? DefaultConstant::CONSTRUCTION_VALUE_CABLE_TRAY_BOTTOM;
        $cableTrayBottom = $cableTrayBottom * 1000;

        $widthCable = $rrlCount * $diameterRrlCable
            + $radioCount * $diameterPowerCable
            + $radioCount * $diameterOpticalCable
            + $otherCount * $diameterOtherEquipmentCable;

        $height = $this->sectionDto->height;
        if ($this->sectionDto->topMark < $cableTrayBottom) {
            $height = 0;
        } elseif ($this->sectionDto->topMark > $cableTrayBottom && $this->sectionDto->buttonMark() < $cableTrayBottom) {
            $height = $this->sectionDto->topMark - $cableTrayBottom;
        }

        $area = $widthCable / 1000 * $height / 1000 * $shadingCoefficient;

        return new PartSectionDto(
            area: $area,
            cx: DefaultConstant::CABLE_CX_COEFFICIENT,
            press: $this->windRegionEnum->pressureKgPerM()
            * $this->terrainTypeEnum->roughnessCoefficient(height: $this->sectionDto->middleMark() / 1000)
            * DefaultConstant::SECURITY_COEFFICIENT
            * $area
            * DefaultConstant::CABLE_CX_COEFFICIENT,
        );
    }
}
