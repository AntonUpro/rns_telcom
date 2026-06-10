<?php

declare(strict_types=1);

namespace App\Dto\Calculation\CalculationResult\Row;

final readonly class FoundationForcesDto
{
    public function __construct(
        public ?float $q,
        public ?float $qU,
        public ?float $beta,
        public ?float $betaU,
        public ?float $kUseStability,
        public ?float $kUseDeformation,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['q'] ?? null,
            $data['qU'] ?? null,
            $data['beta'] ?? null,
            $data['betaU'] ?? null,
            $data['kUseStability'] ?? null,
            $data['kUseDeformation'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'q' => $this->q,
            'qU' => $this->qU,
            'beta' => $this->beta,
            'betaU' => $this->betaU,
            'kUseStability' => $this->kUseStability,
            'kUseDeformation' => $this->kUseDeformation,
        ];
    }

    public function withComputed(?float $kUseStability, ?float $kUseDeformation): self
    {
        return new self(
            $this->q,
            $this->qU,
            $this->beta,
            $this->betaU,
            $kUseStability,
            $kUseDeformation,
        );
    }
}
