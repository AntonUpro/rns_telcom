<?php

declare(strict_types=1);

namespace App\Dto\Calculation\CalculationResult\Row;

final readonly class DeformationRowDto
{
    public function __construct(
        public ?float $mark,
        public ?float $displacement,
        public ?float $angleMax,
        public ?float $angleAllowable = null,
        public ?float $kUse = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['mark'] ?? null,
            $data['displacement'],
            $data['angleMax'] ?? null,
            $data['angleAllowable'] ?? null,
            $data['kUse'] ?? null,
        );
    }

    public function withComputed(?float $kUse): self
    {
        return new self(
            $this->mark,
            $this->displacement,
            $this->angleMax,
            $this->angleAllowable,
            $kUse,
        );
    }

    public function toArray(): array
    {
        return [
            'mark' => $this->mark,
            'displacement' => $this->displacement,
            'angleMax' => $this->angleMax,
            'angleAllowable' => $this->angleAllowable,
            'kUse' => $this->kUse,
        ];
    }
}
