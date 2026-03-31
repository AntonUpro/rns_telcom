<?php

declare(strict_types=1);

namespace App\Dto\Calculation\PillarPlatform;

final class TotalPillarPlatformCalculationDto
{
    public function __construct(
        /** @var PillarPlatformSectionDto[] $platformSections */
        public array $platformSections = [],
    ) {
    }

    public function add(PillarPlatformSectionDto $platformSection): void
    {
        $this->platformSections[] = $platformSection;
    }
}
