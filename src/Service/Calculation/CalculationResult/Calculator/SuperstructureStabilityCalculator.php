<?php

declare(strict_types=1);

namespace App\Service\Calculation\CalculationResult\Calculator;

use App\Dto\Calculation\CalculationResult\Row\SuperstructureStabilityRowDto;
use App\Dto\DefaultConstant;
use App\Entity\Calculation;
use App\Enum\Calculation\BraceConnectionTypeEnum;
use App\Enum\Calculation\FlexibilityTypeEnum;
use App\Enum\Calculation\LoadTypeEnum;
use App\Enum\Calculation\SchemeNumberEnum;
use App\Enum\Gauge\GaugeProfileTypeEnum;
use App\Enum\Pillar\ElementTypeEnum;

/**
 * Заготовка калькулятора устойчивости элементов надстройки.
 *
 * TODO: реализовать проверку устойчивости сжатых/растянутых элементов
 * надстройки по СП 16.13330.2017 (табл. 32 — предельная гибкость,
 * формулы устойчивости сжатых стержней). Пока — pass-through: строки
 * нормализуются через DTO, sigma/kUse не вычисляются (остаются null).
 */
final class SuperstructureStabilityCalculator implements TableCalculatorInterface
{
    public function calculateRows(array $rawRows, ?Calculation $calculation = null): array
    {
        $rows = array_map(
            function (array $raw): array {
                $rawDto = SuperstructureStabilityRowDto::fromArray($raw);

                try {
                    $elementType = ElementTypeEnum::from($rawDto->element);
                    $loadType = LoadTypeEnum::from($rawDto->loadType);
                    $flexibility = FlexibilityTypeEnum::from($rawDto->flexibility);
                    $profileType = GaugeProfileTypeEnum::from($rawDto->profileType);
                    $schemeNumber = SchemeNumberEnum::from($rawDto->schemeNumber);
                    $connectionType = BraceConnectionTypeEnum::from($rawDto->connectionType);

                    $areaCm = $rawDto->area;
                    $momentInertia = $rawDto->momentInertia;
                    $elementLength = $rawDto->elementLength;

                    $ryKgCm = $rawDto->ry * 100 / 9.81;
                    $moduleElasticityKgCm = DefaultConstant::MODULES_ELASTICITY_H_MM * 100 / 9.81;

                    $lambda = $elementLength / (sqrt($momentInertia / $areaCm));
                    $lambda_ = $lambda * sqrt($ryKgCm / $moduleElasticityKgCm);

                    $fiCalc = $this->calcFi($profileType, $loadType, $lambda_);

                    $gammaC = $this->calcGammaC(
                        $profileType,
                        $loadType,
                        $flexibility,
                        $elementType,
                        $schemeNumber,
                        $connectionType,
                    );

                    $nMaxT = $areaCm * $ryKgCm * $fiCalc * $gammaC / 1000;

                    $kUse = $rawDto->nCalc / $nMaxT;

                    $sigma = $rawDto->ry * $kUse;

                    $rawDto = $rawDto->withComputed($sigma, $kUse);
                    return $rawDto->toArray();
                } catch (\Throwable $exception) {
                    return $raw;
                }
            },
            $rawRows,
        );

        return $rows;
    }

    private function calcFi(GaugeProfileTypeEnum $profileType, LoadTypeEnum $loadType, float $lambda_): float
    {
        // если расчет на растяжение, то fi равен 1
        if (! $loadType->isCompress()) {
            return 1;
        }

        $alfa = $profileType->alfa();
        $betta = $profileType->betta();
        $maxLambda_ = $profileType->maxLambda_();

        $delta = 9.87 * (1 - $alfa + $betta * $lambda_) + $lambda_ * $lambda_;

        $fiCalc = 0.5 * ($delta - sqrt($delta * $delta - 39.48 * $lambda_ * $lambda_)) / $lambda_ / $lambda_;
        $fiMax = 7.6 / $lambda_ / $lambda_;

        // условия пунктов 7.1.3 СП16.13330
        if ($lambda_ > $maxLambda_) {
            $fiCalc = min($fiCalc, $fiMax);
        } elseif ($lambda_ < 0.6) {
            $fiCalc = 1;
        }

        return $fiCalc;
    }

    private function calcGammaC(
        GaugeProfileTypeEnum $profileType,
        LoadTypeEnum $loadType,
        FlexibilityTypeEnum $flexibilityType,
        ElementTypeEnum $elementType,
        SchemeNumberEnum $schemeNumber,
        BraceConnectionTypeEnum $braceConnectionType,
    ): float {
        if (! $loadType->isCompress()) {
            return 1;
        }

        // если не уголки, то 1
        if (! in_array($profileType->value, [GaugeProfileTypeEnum::ANGLE_EQUAL->value, GaugeProfileTypeEnum::ANGLE_UNEQUAL->value])) {
            return 1;
        }

        // если одним болтом или через фасонку, то 0,75
        if ($braceConnectionType->value === BraceConnectionTypeEnum::SINGLE_BOLT_OR_GUSSET->value) {
            return 0.75;
        }

        // раскосы по рисунку 15, а и распорки по рисунку 15, б, в, е
        if (
            ($elementType->value === ElementTypeEnum::BRACE->value && SchemeNumberEnum::A->value === $schemeNumber->value)
            || (
                $elementType->value === ElementTypeEnum::SPACER->value
                && in_array($schemeNumber->value, [SchemeNumberEnum::B->value, SchemeNumberEnum::V->value, SchemeNumberEnum::E->value])
            )
        ) {
            return 0.9;
        }

        // раскосы по рисунку 15, в, г, д, е
        if (
            $elementType->value === ElementTypeEnum::BRACE->value
            && in_array(
                $schemeNumber->value,
                [
                    SchemeNumberEnum::B->value,
                    SchemeNumberEnum::G->value,
                    SchemeNumberEnum::D->value,
                    SchemeNumberEnum::E->value,
                ]
            )
        ) {
            return 0.8;
        }

        return 1;
    }
}
