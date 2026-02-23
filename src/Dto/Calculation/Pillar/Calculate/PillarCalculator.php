<?php

declare(strict_types=1);

namespace App\Dto\Calculation\Pillar\Calculate;

use App\Enum\CalculationData\TerrainTypeEnum;
use App\Enum\CalculationData\WindRegionEnum;
use App\Enum\Pillar\PillarEnum;

final class PillarCalculator
{
    public function __construct(
        public WindRegionEnum $windRegion,
        public TerrainTypeEnum $terrainTypeEnum,
        public PillarEnum $pillarEnum,
        public float $heightPillar,
        public float $heightBuilding,
        public float $bottomMark,
        public ?StrengtheningDto $strengtheningDto,
    ) {
    }
}
