<?php

declare(strict_types=1);

namespace App\Entity\JsonData\Dto;

final readonly class DefaultValues
{
    public function __construct(
        public array $cableDiameterValues,
        public array $constructionValues,
        public array $shadingCoefficients,
    ) {
    }

    public function toArray(): array
    {
        return [
            'cableDiameterValues' => $this->cableDiameterValues,
            'constructionValues' => $this->constructionValues,
            'shadingCoefficients' => $this->shadingCoefficients,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['cableDiameterValues'],
            $data['constructionValues'],
            $data['shadingCoefficients'],
        );
    }
}
