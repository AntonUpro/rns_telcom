<?php

declare(strict_types=1);

namespace App\Dto\Calculation\Pillar;

final readonly class PillarCalculationDto
{
    public function __construct(
        public TotalCalculationDataDto $totalCalculationDataDto,
        public PartSectionDto $pillarPart,
        public ?PartSectionDto $cableChanelPart,
        public ?PartSectionDto $cablePart,
        public ?PartSectionDto $ladderPart,
    ) {
    }

    public function toArray(): array
    {
        return [
            'totalCalculationDataDto' => $this->totalCalculationDataDto->toArray(),
            'pillarPart' => $this->pillarPart->toArray(),
            'cableChanelPart' => $this->cableChanelPart?->toArray(),
            'cablePart' => $this->cablePart?->toArray(),
            'ladderPart' => $this->ladderPart?->toArray(),
        ];
    }
}
