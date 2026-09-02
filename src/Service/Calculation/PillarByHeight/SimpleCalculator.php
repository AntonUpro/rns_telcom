<?php

declare(strict_types=1);

namespace App\Service\Calculation\PillarByHeight;

use App\Dto\Calculation\PillarByHeight\SimpleResultDto;
use App\Entity\Calculation;
use App\Entity\JsonData\Dto\Strengthening;
use App\Enum\Calculation\ResultTableTypeEnum;
use App\Enum\Pillar\PillarEnum;
use App\Service\DocumentGenerator\Report\Data\PillarSectionTableData;
use App\Service\DocumentGenerator\Report\ReportContext;
use InvalidArgumentException;

final readonly class SimpleCalculator
{
    private const float MASS_SUM_PILLAR_AND_EQUIPMENT = 1500;

    /**
     * @param ReportContext $context
     * @return SimpleResultDto[]
     */
    public function calculate(ReportContext $context): array
    {
        $specificData = $context->calculation->getCalculationData()?->getConcretePillarSpecificData();

        $momentsByMarkMm = [];
        foreach ($context->calculation?->getCalculationResultTables()?->toArray() ?? [] as $table) {
            if ($table->getTableType() === ResultTableTypeEnum::PILLAR_FORCES) {
                foreach ($table->getRows() as $row) {
                    if (isset($row['mCalc']) && isset($row['mark'])) {
                        $mark = (int)($row['mark'] * 1000);
                        $momentsByMarkMm[$mark] = $row['mCalc'];
                    }
                }
            }
        }

        $pillar = $specificData->toEnumPillar();
        $pillarHeightM = $specificData->pillarHeight;

        $result = [];

        foreach ($momentsByMarkMm as $mark => $moment) {
            $markM = $mark/1000;
            $result[] = $this->calculateSimpleResultDto($pillar, $pillarHeightM, $markM, $moment, $specificData->strengthening);
        }

        return $result;
    }

    /**
     * Считает допустимый момент (MAdditional) отдельно для каждой целой метровой
     * отметки от 0 (уровень земли) до последней отметки перед вершиной столба.
     *
     * В отличие от calculate(), отметки здесь — это чистые метры над землёй
     * (0, 1, 2, ...), а не смещения, привязанные к таблице армирования по полной
     * высоте — поэтому индексация строк армирования ведётся по $hFull
     * (высота от низа стойки, включая заглублённую часть), а не по самой отметке.
     *
     * @return array<int, float> mark => MAdditional (тс·м); [] если для марки нет
     *         данных армирования по высоте (PillarSectionTableData)
     */
    /**
     * Последняя метровая отметка над землёй, которая должна попасть в таблицу:
     * для целой высоты — на метр меньше высоты, для дробной — целая часть высоты.
     * Например, 23 м → 22, 22.5 м → 22.
     */
    public static function lastMarkAboveGround(float $pillarHeightM): int
    {
        if ($pillarHeightM <= 0) {
            return -1;
        }

        return ((float)((int)$pillarHeightM) === $pillarHeightM)
            ? (int)$pillarHeightM - 1
            : (int)floor($pillarHeightM);
    }

    /**
     * @param PillarEnum $pillar
     * @param float $pillarHeightM
     * @param float $maxMoment
     * @param array $momentByMark
     * @return array{int: float}
     *
     */
    public function calculateAllowableMomentsByHeight(Calculation $calculation, array $momentByMark): array
    {
        $specificData = $calculation->getCalculationData()?->getConcretePillarSpecificData();
        $pillarHeightM = $specificData?->pillarHeight;

        try {
            $pillar = $specificData->toEnumPillar();
        } catch (InvalidArgumentException) {
            return [];
        }

        if ($specificData === null || $pillarHeightM === null || $pillarHeightM <= 0 || count($momentByMark) === 0) {
            return [];
        }

        $result = [];

        foreach ($momentByMark as $mark => $moment) {
            $markM = $mark / 1000;

            $simpleResultDto = $this->calculateSimpleResultDto($pillar, $pillarHeightM, $markM, $moment, $specificData->strengthening);

            $result[$mark] = $simpleResultDto->MAdditional;
        }

        return $result;
    }

    private function calculateSimpleResultDto(PillarEnum $pillar, float $pillarHeightM, float $markM, float $momentFact, ?Strengthening $strengthening): ?SimpleResultDto
    {
        try {
            $rows = PillarSectionTableData::getRows($pillar);
            $areaPrestressingReinforcement = PillarSectionTableData::getAreaPrestressingReinforcement($pillar);
            $areaNonPrestressingReinforcement = PillarSectionTableData::getAreaNonPrestressingReinforcement($pillar);
            [$Rsp, $Rs, $Rsc, , $Eb] = PillarSectionTableData::getMaterial($pillar);
            $Rb = PillarSectionTableData::getCalculatedConcreteResistance($pillar);
            $maxMomentDefault = $pillar->getAllowableMomentByStrength();
        } catch (InvalidArgumentException) {
            return null;
        }

        $strengtheningMoment = 0;
        if ($strengthening && $strengthening->strengtheningHeight > $markM) {
            $strengtheningMoment = $strengthening->allowedMoment;
        }

        $moment = $momentFact ?: ($maxMomentDefault - ($maxMomentDefault * $markM / $pillarHeightM));

        $fullPillarLengthM = $pillar->getHeight() / 1000;
        $depthPillar = $fullPillarLengthM - $pillarHeightM;

        $hFull = (int)($depthPillar + $markM);

        [$countPrestressingReinforcement, $countNonPrestressingReinforcement] = $rows[$markM];
        $Asp = $countPrestressingReinforcement * $areaPrestressingReinforcement;
        $As = $countNonPrestressingReinforcement * $areaNonPrestressingReinforcement;

        $heightFromBottomMm = $hFull * 1000.0;
        $D = $pillar->getDiameterAtHeight($heightFromBottomMm);
        $d = $pillar->getInternalDiameterAtHeight($heightFromBottomMm);
        $AsTot = $Asp + $As;
        $A = M_PI / 4.0 * ($D ** 2 - $d ** 2) / 1e6;

        $pillarMass = $pillar->getMass() / 1000;
        $N = ($pillarMass - ($markM + $depthPillar) / $fullPillarLengthM * $pillarMass) * 1000 * 9.81 + 9.81 * self::MASS_SUM_PILLAR_AND_EQUIPMENT;

        $tension = $pillar->getTension();

        $rm = 0.5 * $d / 2 / 1000 + 0.5 * $D / 2 / 1000;
        $Is = ($Asp * $rm ** 2) / 2 * 100 + ($As * $rm ** 2) / 2 * 100;
        $I = M_PI * (($D / 2 * 1000) ** 4 - ($d / 2 * 1000) ** 4) / (4 * 1000000);
        $Areg = $A * 1000000 + 5.28 * $Asp * 100 + 5.28 * $As * 100;

        // Рассчитываем потери натяжения
        $q1 = 50.6;
        $q2 = 0;
        $q3 = 14.6;
        $q4 = 0;
        $q5 = 30;

        $qbp = $tension * 1000 * 0.9 / $Areg;
        $qbpRbp = $qbp / $Rb;

        $q6 = 34 * $qbpRbp / 0.85;

        $qSum1_6 = $q1 + $q2 + $q3 + $q4 + $q5 + $q6;
        $tensionWithLosses1_6 = $tension * 10 / $Asp - $qSum1_6;
        $qBpWithLosses1_6 = $Asp * $tensionWithLosses1_6 * 100 / $Areg;
        $qbp1Rbp = $qBpWithLosses1_6 / $Rb;

        $q7 = 0;
        $q8 = 50;
        $q9 = 128 * 1.17 * $qbp1Rbp;

        $totalLosses = $q1 + $q2 + $q3 + $q4 + $q5 + $q6 + $q7 + $q8 + $q9;

        $tensionSp = $tension * 10 / $Asp - $totalLosses;

        $rsRsp = ($D / 2 - 24) / 1000;
        $fi1 = 1 + $N * $rm / ($N * $rm + $moment);
        $e0 = $moment / $N;
        $e0D = $e0 * 1000 / $D;
        $qbp = $Asp * $tensionSp * 0.9 * 100 / $Areg;
        $fip = 1 + 12 * $qbp * 1.5 / $Rb;
        $Ncr = (6.4 * $Eb / 26000 ** 2) * ($I * 1000000 * (0.11 / (0.1 + 1.5 * $fip) + 0.1) / $fi1 + 5.55 * $Is * 1000000) / 1000;
        $nu = 1 / (1 - $N / ($Ncr * 1000));
        $deltaSP = 1.5 + 6 * $Rsp * 10 ** (-4);
        $deltaS = 1.5 + 6 * $Rs * 10 ** (-4);
        $Wp = 1.1 - $tensionSp / $Rs;
        $Ws = 1.1;
        $Ecir = ($N + ($tensionSp + $Wp * $Rs) * $Asp * 100 + $Ws * $Rs * $As * 100) / ($Rb * $A * 1000000 + ($Rsc + $deltaSP * $Wp * $Rs) * $Asp * 100 + ($Rsc + $deltaSP * $Ws * $Rs) * $As * 100);
        $zsZsp = (0.2 + 1.3 * $Ecir) * $rsRsp * 1000;
        $fiSp = $Wp * (1 - $deltaSP * $Ecir);
        $fiS = $Ws * (1 - $deltaSP * $Ecir);
        $MAdditional = (($Rb * $A * 1000000 * $rm * 1000 + $Rsc * $AsTot * 100 * $zsZsp) * sin(M_PI * $Ecir) / M_PI + $Rs * $AsTot * 100 * $fiS * $zsZsp) / ($nu * 9.81 * 1000000);
        $MAdditional += $strengtheningMoment;

        return new SimpleResultDto(
            mark: $markM,
            countPrestressingReinforcement: $countPrestressingReinforcement,
            Asp: $Asp,
            countNonPrestressingReinforcement: $countNonPrestressingReinforcement,
            As: $As,
            tensionSp: $tensionSp,
            D: $D,
            d: $d,
            N: $N,
            Rsp: $Rsp,
            Rs: $Rs,
            Rsc: $Rsc,
            Rb: $Rb,
            Eb: $Eb,
            AsTot: $AsTot,
            APillar: $A,
            r1: $D / 2,
            r2: $d / 2,
            I: $I,
            Is: $Is,
            Areg: $Areg,
            rm: $rm,
            rsRsp: $rsRsp,
            fi1: $fi1,
            e0: $e0,
            e0D: $e0D,
            qbp: $qbp,
            fip: $fip,
            Ncr: $Ncr,
            nu: $nu,
            deltaSP: $deltaSP,
            deltaS: $deltaS,
            Wp: $Wp,
            Ws: $Ws,
            Ecir: $Ecir,
            zsZsp: $zsZsp,
            fiSp: $fiSp,
            fiS: $fiS,
            MAdditional: $MAdditional,
            MFactH: $moment * 1000 * 9.81,
            MFactKg: $moment,
            k: $moment / $MAdditional,
        );
    }
}
