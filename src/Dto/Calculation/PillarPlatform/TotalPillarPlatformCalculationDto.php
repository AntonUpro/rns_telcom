<?php

declare(strict_types=1);

namespace App\Dto\Calculation\PillarPlatform;

final readonly class TotalPillarPlatformCalculationDto
{
    public function __construct(
        /** @var  */
        public array $platformSections
    ) {
    }
}
