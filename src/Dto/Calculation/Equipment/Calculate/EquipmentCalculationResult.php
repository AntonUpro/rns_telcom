<?php

declare(strict_types=1);

namespace App\Dto\Calculation\Equipment\Calculate;

final readonly class EquipmentCalculationResult
{
    public function __construct(
        public string $fullName,
        public int $quantity,
        public float $monthHeight,
        public float $kze,
        public float $oneEquipmentArea,
        public float $pipeStandArea,
        public float $windPress,
        public float $securityCoefficient,
        public float $cxInf,
        public float $kLambda,
        public float $cxEquipment,
        public float $cxPipeStand,
        public float $shadingCoefficient,
        public float $pressOnOneEquipment,
    ) {
    }

    public function toArray(): array
    {
        return [
            'fullName' => $this->fullName,
            'quantity' => $this->quantity,
            'monthHeight' => $this->monthHeight,
            'kze' => $this->kze,
            'oneEquipmentArea' => $this->oneEquipmentArea,
            'pipeStandArea' => $this->pipeStandArea,
            'windPress' => $this->windPress,
            'securityCoefficient' => $this->securityCoefficient,
            'cxInf' => $this->cxInf,
            'kLambda' => $this->kLambda,
            'cxEquipment' => $this->cxEquipment,
            'cxPipeStand' => $this->cxPipeStand,
            'shadingCoefficient' => $this->shadingCoefficient,
            'pressOnOneEquipment' => $this->pressOnOneEquipment,
        ];
    }
}
