<?php

declare(strict_types=1);

namespace App\Dto\Calculation\Pillar;

final readonly class TotalCalculationDataDto
{
    public function __construct(
        public int $numberSection,
        public float $heightSection,
        public float $kze,
        public float $windPress,
        public float $shadingCoefficient,
        public float $topHeight,
    ) {
    }

    public function toArray(): array
    {
        return [
            'numberSection' => $this->numberSection,
            'heightSection' => $this->heightSection,
            'kze' => round($this->kze,2),
            'windPress' => $this->windPress,
            'shadingCoefficient' => $this->shadingCoefficient,
            'topHeight' => $this->topHeight,
        ];
    }
}
