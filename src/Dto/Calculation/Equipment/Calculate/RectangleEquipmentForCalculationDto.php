<?php

declare(strict_types=1);

namespace App\Dto\Calculation\Equipment\Calculate;

use App\Enum\CalculationData\TerrainTypeEnum;
use App\Enum\CalculationData\WindRegionEnum;

final class RectangleEquipmentForCalculationDto extends AbstractCalculatorEquipment
{
    // коэффициент затенения для прямоугольных антенн
    const SHADING_COEFFICIENT = 0.8;

    public function __construct(
        public readonly float $height,
        public readonly float $width,
        public readonly float $depth,
        public readonly float $weight,
    ) {
    }

    /**
     * @return float в метрах квадратных
     */
    public function calcArea(): float
    {
        return ($this->height / 1000) * ($this->width / 1000);
    }

    public function calcAb(): float
    {
        return $this->depth / $this->height;
    }

    public function calcLambdaE(): float
    {
        return $this->height / $this->width;
    }
}
