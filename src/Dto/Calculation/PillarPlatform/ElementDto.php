<?php

declare(strict_types=1);

namespace App\Dto\Calculation\PillarPlatform;

use App\Dto\DefaultConstant;
use App\Enum\Pillar\ElementTypeEnum;
use App\Enum\Pillar\SectionConstructTypeEnum;
use App\Service\Calculation\CxCylinder;

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
        return $this->with / 1000 * $this->length / 1000 * $this->count;
    }

    public function calcAiCxi(): float
    {
        return $this->with / 1000 * $this->sectionConstructType->cx();
    }

    /**
     * Аэродинамический коэффициент Сх. Для круглых сечений считается
     * по числу Рейнольдса (СП 20.13330.2016, прил. В) через CxCylinder,
     * для остальных сечений — фиксированное табличное значение.
     */
    public function cx(float $windPressureKgPerM, float $kze): float
    {
        if (
            $this->sectionConstructType !== SectionConstructTypeEnum::ROUND_PIPE
            && $this->sectionConstructType !== SectionConstructTypeEnum::ROUND
        ) {
            return $this->sectionConstructType->cx();
        }

        $re = $this->calcRe($windPressureKgPerM, $kze);
        if ($re < 100000) {
            return 1.2;
        }

        return CxCylinder::getCx($re, $this->calcDelta());
    }

    private function calcRe(float $windPressureKgPerM, float $kze): float
    {
        return 0.88
            * $this->with / 1000
            * sqrt($windPressureKgPerM * $kze * DefaultConstant::SECURITY_COEFFICIENT)
            * 100000;
    }

    private function calcDelta(): float
    {
        return 1 / $this->with;
    }
}
