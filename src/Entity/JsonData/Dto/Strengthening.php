<?php

declare(strict_types=1);

namespace App\Entity\JsonData\Dto;

class Strengthening
{
    public function __construct(
        public readonly string $strengtheningGeometry,
        public readonly float $strengtheningWidth,
        public readonly float $strengtheningHeight,
        public readonly float $allowedMoment,
    ) {
    }

    public function toArray()
    {
        return [
            'strengtheningGeometry' => $this->strengtheningGeometry,
            'strengtheningWidth' => $this->strengtheningWidth,
            'strengtheningHeight' => $this->strengtheningHeight,
            'allowedMoment' => $this->allowedMoment,
        ];
    }

    public static function fromArray(array $data)
    {
        return new self(
            $data['strengtheningGeometry'],
            $data['strengtheningWidth'],
            $data['strengtheningHeight'],
            $data['allowedMoment'],
        );
    }
}
