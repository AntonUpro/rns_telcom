<?php

declare(strict_types=1);

namespace App\Dto\Calculation\PillarPlatform;

final readonly class ElementsCollectionDto
{
    /**
     * @param ElementDto[] $elements
     */
    public function __construct(
        public array $elements
    ) {
    }

    public function sumElementArea(): float
    {
        $area = 0;
        foreach ($this->elements as $element) {
            $area += $element->areaElements();
        }

        return $area;
    }

    public function sumAiCxi(): float
    {
        $sum = 0;
        foreach ($this->elements as $element) {
            $sum += $element->calcAiCxi() * $element->areaElements();
        }

        return $sum;
    }
}
