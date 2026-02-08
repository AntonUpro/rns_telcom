<?php

declare(strict_types=1);

namespace App\Dto\Calculation\Equipment\Calculate;

class RoundEquipmentForCalculationDto extends AbstractCalculatorEquipment
{

    public function __construct(
        public float $diameter, // в мм
        public float $weight,
    ) {
    }

    public function calcArea(): float
    {
        return pi() * ($this->diameter / 1000 / 2) ** 2;
    }

    public function calcAb(): float
    {
        return $this->diameter / ($this->diameter * 2 / 3.2);
    }

    public function calcLambdaE(): float
    {
        return $this->diameter / $this->diameter;
    }
}
