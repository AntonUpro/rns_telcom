<?php

declare(strict_types=1);

namespace App\Dto\Calculation\PillarPlatform;

final readonly class PillarPlatformSectionDto
{
    public function __construct(
        public int $numberSection,
        public int $heightSection,
        public int $mountingHeightSection,
        public float $areaContourSection,
        public float $kze,
        public float $cx,
        public float $fi,
        public float $nu,
        public float $ct,
        public float $windPress,
        public float $shadingCoefficient,
        public float $press,
        public ElementsCollectionDto $elementsCollectionDto,
    ) {
    }


}
