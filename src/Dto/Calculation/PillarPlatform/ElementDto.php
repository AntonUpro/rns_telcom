<?php

declare(strict_types=1);

namespace App\Dto\Calculation\PillarPlatform;

use App\Enum\Pillar\ElementTypeEnum;
use App\Enum\Pillar\SectionConstructTypeEnum;

final readonly class ElementDto
{
    public function __construct(
        public ElementTypeEnum $elementType,
        public SectionConstructTypeEnum $sectionConstructType,
        public float $with,
        public int $length,
        public int $count,
    ) {
    }

    public function areaElements(): float
    {
        return $this->with * $this->length * $this->count;
    }

    public function calcAiCxi(): float
    {
        return $this->with / 1000 * $this->sectionConstructType->cx();
    }
}
