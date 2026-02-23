<?php

declare(strict_types=1);

namespace App\Dto\Calculation\Pillar;

final readonly class PartSectionDto
{
    public function __construct(
        public float $area,
        public float $cx,
        public float $press,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['area'],
            $data['cx'],
            $data['press'],
        );
    }

    public function toArray(): array
    {
        return [
            'area' => round($this->area, 2),
            'cx' => round($this->cx, 2),
            'press' => round($this->press, 2),
        ];
    }
}
