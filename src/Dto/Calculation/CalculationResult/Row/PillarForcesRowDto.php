<?php

declare(strict_types=1);

namespace App\Dto\Calculation\CalculationResult\Row;

final readonly class PillarForcesRowDto
{
    public function __construct(
        public ?float $mark,
        public string $pillarType,
        public ?float $mCalc,
        public ?float $mAllowable = null,
        public ?float $kMax = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            mark: isset($data['mark']) ? (float)$data['mark'] : null,
            pillarType: (string)($data['pillarType'] ?? ''),
            mCalc: isset($data['mCalc']) ? (float)$data['mCalc'] : null,
            mAllowable: isset($data['mAllowable']) ? (float)$data['mAllowable'] : null,
            kMax: isset($data['kMax']) ? (float)$data['kMax'] : null,
        );
    }

    public function withComputed(float $mAllowable, ?float $kMax): self
    {
        return new self(
            mark: $this->mark,
            pillarType: $this->pillarType,
            mCalc: $this->mCalc,
            mAllowable: $mAllowable,
            kMax: $kMax,
        );
    }

    public function toArray(): array
    {
        return [
            'mark' => $this->mark,
            'pillarType' => $this->pillarType,
            'mCalc' => $this->mCalc,
            'mAllowable' => $this->mAllowable,
            'kMax' => $this->kMax,
        ];
    }
}
