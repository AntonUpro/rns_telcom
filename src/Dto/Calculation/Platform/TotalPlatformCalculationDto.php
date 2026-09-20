<?php

declare(strict_types=1);

namespace App\Dto\Calculation\Platform;

final class TotalPlatformCalculationDto
{
    public function __construct(
        /** @var PlatformSectionDto[] $platformSections */
        public array $platformSections = [],
    ) {
    }

    public function add(PlatformSectionDto $platformSection): void
    {
        $this->platformSections[] = $platformSection;
    }
}
