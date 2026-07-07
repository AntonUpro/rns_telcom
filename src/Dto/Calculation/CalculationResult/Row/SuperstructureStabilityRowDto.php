<?php

declare(strict_types=1);

namespace App\Dto\Calculation\CalculationResult\Row;

final readonly class SuperstructureStabilityRowDto
{
    public function __construct(
        public string $element,
        public ?float $mark,
        public string $profileType,
        public string $sectionDesignation,
        public ?float $elementLength, // длина элемента, см
        public string $loadType, // LoadTypeEnum
        public string $connectionType, // BraceConnectionTypeEnum
        public ?string $schemeNumber, // SchemeNumberEnum
        public ?string $flexibility, // FlexibilityTypeEnum
        public ?float $area,
        public ?float $momentInertia, // Iy, см⁴
        public ?float $nCalc, // в тоннах
        public ?float $ry,
        public ?float $sigma = null,
        public ?float $kUse = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            element: (string)($data['element'] ?? ''),
            mark: isset($data['mark']) ? (float)$data['mark'] : null,
            profileType: (string)($data['profileType'] ?? ''),
            sectionDesignation: (string)($data['sectionDesignation'] ?? ''),
            elementLength: isset($data['elementLength']) ? (float)$data['elementLength'] : null,
            loadType: (string)($data['loadType'] ?? ''),
            connectionType: (string)($data['connectionType'] ?? ''),
            schemeNumber: isset($data['schemeNumber']) ? (string)$data['schemeNumber'] : null,
            flexibility: isset($data['flexibility']) ? (string)$data['flexibility'] : null,
            area: isset($data['area']) ? (float)$data['area'] : null,
            momentInertia: isset($data['momentInertia']) ? (float)$data['momentInertia'] : null,
            nCalc: isset($data['nCalc']) ? (float)$data['nCalc'] : null,
            ry: isset($data['ry']) ? (float)$data['ry'] : null,
            sigma: isset($data['sigma']) ? (float)$data['sigma'] : null,
            kUse: isset($data['kUse']) ? (float)$data['kUse'] : null,
        );
    }

    public function withComputed(?float $sigma, ?float $kUse): self
    {
        return new self(
            element: $this->element,
            mark: $this->mark,
            profileType: $this->profileType,
            sectionDesignation: $this->sectionDesignation,
            elementLength: $this->elementLength,
            loadType: $this->loadType,
            connectionType: $this->connectionType,
            schemeNumber: $this->schemeNumber,
            flexibility: $this->flexibility,
            area: $this->area,
            momentInertia: $this->momentInertia,
            nCalc: $this->nCalc,
            ry: $this->ry,
            sigma: $sigma,
            kUse: $kUse,
        );
    }

    public function toArray(): array
    {
        return [
            'element' => $this->element,
            'mark' => $this->mark,
            'profileType' => $this->profileType,
            'sectionDesignation' => $this->sectionDesignation,
            'elementLength' => $this->elementLength,
            'loadType' => $this->loadType,
            'connectionType' => $this->connectionType,
            'schemeNumber' => $this->schemeNumber,
            'flexibility' => $this->flexibility,
            'area' => $this->area,
            'momentInertia' => $this->momentInertia,
            'nCalc' => $this->nCalc,
            'ry' => $this->ry,
            'sigma' => $this->sigma,
            'kUse' => $this->kUse,
        ];
    }
}
