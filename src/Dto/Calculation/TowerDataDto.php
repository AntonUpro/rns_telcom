<?php

declare(strict_types=1);

namespace App\Dto\Calculation;

use Symfony\Component\Validator\Constraints as Assert;

class TowerDataDto
{
    public function __construct(
        #[Assert\Type('numeric')]
        public ?float $markBottom = null,

        #[Assert\Type('numeric')]
        #[Assert\Range(min: 0)]
        public ?float $towerHeight = null,

        #[Assert\Choice(choices: [3, 4])]
        public int $facetsCount = 3,
    ) {
    }

    public function toArray(): array
    {
        return [
            'markBottom' => $this->markBottom,
            'towerHeight' => $this->towerHeight,
            'facetsCount' => $this->facetsCount,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            markBottom: $data['markBottom'] ?? null,
            towerHeight: $data['towerHeight'] ?? null,
            facetsCount: $data['facetsCount'] ?? 3,
        );
    }

    public function isEmpty(): bool
    {
        return $this->markBottom === null && $this->towerHeight === null;
    }
}
