<?php

declare(strict_types=1);

namespace App\Dto\Calculation\Equipment\Calculate;

final readonly class EquipmentSummaryResult
{
    public function __construct(
        public string $label,
        public float $totalArea,
        public float $totalPressure,
        public float $totalWeight,
    ) {
    }
}
