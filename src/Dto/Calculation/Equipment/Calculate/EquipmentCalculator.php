<?php

declare(strict_types=1);

namespace App\Dto\Calculation\Equipment\Calculate;

use App\Enum\CalculationData\TerrainTypeEnum;
use App\Enum\CalculationData\WindRegionEnum;
use App\Enum\Equipment\EquipmentTypeEnum;

final readonly class EquipmentCalculator
{
    public const SECURITY_COEFFICIENT = 1.4;

    public function __construct(
        public AbstractCalculatorEquipment $equipment,
        public WindRegionEnum $windRegion,
        public TerrainTypeEnum $terrainTypeEnum,
        public float $mountHeight,
        public EquipmentTypeEnum $equipmentTypeEnum,
        public int $quantity,
    ) {
    }

    public function pressOnOneEquipment(): float
    {
        return $this->equipment->calcArea() // площадь
            * $this->getKze() // k(z)
            * $this->windRegion->pressureKgPerM() // ветровое давление
            * self::SECURITY_COEFFICIENT // коэффициент надежности
            * $this->calculateShading() // коэффициент затмения
            * $this->equipment->calcCX() // коэффициент Cx
            ;
    }

    public function getKze(): float
    {
        return $this->terrainTypeEnum->roughnessCoefficient($this->mountHeight);
    }

    public function totalLoad(): float
    {
        return $this->pressOnOneEquipment() * $this->quantity;
    }

    public function calculateShading(): float
    {
        return match ($this->equipmentTypeEnum) {
            EquipmentTypeEnum::RRL => 1,
            EquipmentTypeEnum::PANEL => 0.8,
            EquipmentTypeEnum::RADIO => 0.7,
            EquipmentTypeEnum::OTHER => 0.8,
        };
    }
}
