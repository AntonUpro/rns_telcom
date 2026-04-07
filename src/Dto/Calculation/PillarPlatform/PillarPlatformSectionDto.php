<?php

declare(strict_types=1);

namespace App\Dto\Calculation\PillarPlatform;

use App\Enum\Pillar\PlatformSectionTypeEnum;

final readonly class PillarPlatformSectionDto
{
    public function __construct(
        public int $numberSection,
        public PlatformSectionTypeEnum $type,
        public int $heightSection,
        public int $mountingHeightSection,
        public int $widthBottom,
        public int $widthTop,
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
