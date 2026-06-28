<?php

declare(strict_types=1);

namespace App\Dto\Calculation\PillarByHeight;

class SimpleResultDto
{
    public function __construct(
        public float $mark,
        public int $countPrestressingReinforcement,
        public float $Asp,
        public int $countNonPrestressingReinforcement,
        public float $As,
        public float $tensionSp,
        public float $D,
        public float $d,
        public float $N,
        public float $Rsp,
        public float $Rs,
        public float $Rsc,
        public float $Rb,
        public float $Eb,
        public float $AsTot,
        public float $APillar,
        public float $r1,
        public float $r2,
        public float $I,
        public float $Is,
        public float $Areg,
        public float $rm,
        public float $rsRsp,
        public float $fi1,
        public float $e0,
        public float $e0D,
        public float $qbp,
        public float $fip,
        public float $Ncr,
        public float $nu,
        public float $deltaSP,
        public float $deltaS,
        public float $Wp,
        public float $Ws,
        public float $Ecir,
        public float $zsZsp,
        public float $fiSp,
        public float $fiS,
        public float $MAdditional,
        public float $MFactH,
        public float $MFactKg,
        public float $k,
    ) {
    }
}
