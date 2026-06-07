<?php

declare(strict_types=1);

namespace App\Dto\Calculation\CalculationResult\Row;

final readonly class StressRowDto
{
    public function __construct(
        public string  $element,
        public ?float  $mark,
        public string  $profileType,
        public string  $sectionDesignation,
        public ?float  $area,
        public ?float  $momentResistance,
        public ?float  $nCalc, // в тоннах
        public ?float  $mCalc, // в тоннах на метр
        public ?float  $ry,
        public ?float  $sigma = null,
        public ?float  $kUse = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            element:            (string) ($data['element']            ?? ''),
            mark:               isset($data['mark'])               ? (float) $data['mark']               : null,
            profileType:        (string) ($data['profileType']        ?? ''),
            sectionDesignation: (string) ($data['sectionDesignation'] ?? ''),
            area:               isset($data['area'])               ? (float) $data['area']               : null,
            momentResistance:   isset($data['momentResistance'])   ? (float) $data['momentResistance']   : null,
            nCalc:              isset($data['nCalc'])              ? (float) $data['nCalc']              : null,
            mCalc:              isset($data['mCalc'])              ? (float) $data['mCalc']              : null,
            ry:                 isset($data['ry'])                 ? (float) $data['ry']                 : null,
            sigma:              isset($data['sigma'])              ? (float) $data['sigma']              : null,
            kUse:               isset($data['kUse'])               ? (float) $data['kUse']              : null,
        );
    }

    public function withComputed(?float $sigma, ?float $kUse): self
    {
        return new self(
            element:            $this->element,
            mark:               $this->mark,
            profileType:        $this->profileType,
            sectionDesignation: $this->sectionDesignation,
            area:               $this->area,
            momentResistance:   $this->momentResistance,
            nCalc:              $this->nCalc,
            mCalc:              $this->mCalc,
            ry:                 $this->ry,
            sigma:              $sigma,
            kUse:               $kUse,
        );
    }

    public function toArray(): array
    {
        return [
            'element'            => $this->element,
            'mark'               => $this->mark,
            'profileType'        => $this->profileType,
            'sectionDesignation' => $this->sectionDesignation,
            'area'               => $this->area,
            'momentResistance'   => $this->momentResistance,
            'nCalc'              => $this->nCalc,
            'mCalc'              => $this->mCalc,
            'ry'                 => $this->ry,
            'sigma'              => $this->sigma,
            'kUse'               => $this->kUse,
        ];
    }
}
