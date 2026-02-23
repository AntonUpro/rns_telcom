<?php

declare(strict_types=1);

namespace App\Service\Calculation\Pillar\Calculator;

use App\Calculator\CalculatorKLambda;
use App\Dto\Calculation\Pillar\Calculate\SectionDto;
use App\Dto\Calculation\Pillar\PartSectionDto;
use App\Dto\DefaultConstant;
use App\Enum\CalculationData\TerrainTypeEnum;
use App\Enum\CalculationData\WindRegionEnum;
use App\Enum\Pillar\PillarEnum;

final class PillarCalculator
{
    public function __construct(
        private SectionDto $sectionDto,
        private WindRegionEnum $windRegionEnum,
        private TerrainTypeEnum $terrainTypeEnum,
        private PillarEnum $pillarEnum,
        private float $height,
    ) {
    }

    public function calculate(): PartSectionDto
    {
        $area = $this->sectionDto->getContourArea() / 1000 / 1000;
        $cx = $this->calcCx();

        $press = $this->windRegionEnum->pressureKgPerM()
            * $this->terrainTypeEnum->roughnessCoefficient(height: $this->sectionDto->middleMark() / 1000)
            * DefaultConstant::SECURITY_COEFFICIENT
            * $area
            * $cx;

        return new PartSectionDto(
            area: $area,
            cx: $cx,
            press: $press,
        );
    }

    /**
     * Подсчет числа Рейнольдса
     */
    private function calcRe(): float
    {
        return 0.88
            * $this->sectionDto->getAverageDiameter() / 1000
            * sqrt(
                $this->windRegionEnum->pressureKgPerM()
                * $this->terrainTypeEnum->roughnessCoefficient(height: $this->sectionDto->middleMark() / 1000)
                * DefaultConstant::SECURITY_COEFFICIENT
            ) * 100000;
    }

    private function calcCx(): float
    {
        $KLambda = $this->calcKLambda();
        return $this->calcCxInf() * $KLambda;
    }

    private function calcCxInf(): float
    {
        return $this->calcRe() < 100000
            ? 1.2
            : 0.9; // TODO сложный расчет переделать
    }

    private function calcKLambda(): float
    {
        $lambdaE = $this->pillarEnum->calcLambdaE($this->height);
        return CalculatorKLambda::calc($lambdaE);
    }
}
