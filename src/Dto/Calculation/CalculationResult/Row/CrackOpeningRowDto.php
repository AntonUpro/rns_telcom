<?php

declare(strict_types=1);

namespace App\Dto\Calculation\CalculationResult\Row;

final readonly class CrackOpeningRowDto
{
    public function __construct(
        public ?float $mark,
        public string $pillarType,
        public ?float $crackWidthAllowable,
        public ?float $crackWidthCalc = null,
        public ?float $kMax = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            mark: isset($data['mark']) ? (float)$data['mark'] : null,
            pillarType: (string)($data['pillarType'] ?? ''),
            crackWidthAllowable: isset($data['crackWidthAllowable']) ? (float)$data['crackWidthAllowable'] : null,
            crackWidthCalc: isset($data['crackWidthCalc']) ? (float)$data['crackWidthCalc'] : null,
            kMax: isset($data['kMax']) ? (float)$data['kMax'] : null,
        );
    }

    public function withComputed(?float $crackWidthCalc, ?float $kMax): self
    {
        return new self(
            mark: $this->mark,
            pillarType: $this->pillarType,
            crackWidthAllowable: $this->crackWidthAllowable,
            crackWidthCalc: $crackWidthCalc,
            kMax: $kMax,
        );
    }

    public function toArray(): array
    {
        return [
            'mark' => $this->mark,
            'pillarType' => $this->pillarType,
            'crackWidthAllowable' => $this->crackWidthAllowable,
            'crackWidthCalc' => $this->crackWidthCalc,
            'kMax' => $this->kMax,
        ];
    }
}
