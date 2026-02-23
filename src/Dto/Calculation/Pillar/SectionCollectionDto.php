<?php

declare(strict_types=1);

namespace App\Dto\Calculation\Pillar;

final class SectionCollectionDto
{
    /**
     * @var PillarCalculationDto[]
     */
    private array $sections;

    public function add(PillarCalculationDto $pillarCalculationDto): void
    {
        $this->sections[] = $pillarCalculationDto;
    }

    /**
     * @return PillarCalculationDto[]
     */
    public function getSections(): array
    {
        return $this->sections;
    }

    public function toArray(): array
    {
        return array_map(
            fn (PillarCalculationDto $pillarCalculationDto) => $pillarCalculationDto->toArray(),
            $this->sections
        );
    }
}
