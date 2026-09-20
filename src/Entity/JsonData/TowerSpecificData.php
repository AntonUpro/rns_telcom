<?php

declare(strict_types=1);

namespace App\Entity\JsonData;

use App\Entity\JsonData\Dto\DefaultValues;

final class TowerSpecificData extends AbstractJsonData
{
    public function __construct(
        public readonly ?float $markBottom,
        public readonly ?float $towerHeight,
        public readonly int $facetsCount,
        public readonly ?DefaultValues $defaultValues,
    ) {
    }

    public function toArray(): array
    {
        return [
            'markBottom' => $this->markBottom,
            'towerHeight' => $this->towerHeight,
            'facetsCount' => $this->facetsCount,
            'defaultValues' => $this->defaultValues?->toArray(),
        ];
    }

    public static function fromArray(array $data): static
    {
        return new static(
            markBottom: $data['markBottom'] ?? null,
            towerHeight: $data['towerHeight'] ?? null,
            facetsCount: $data['facetsCount'] ?? 3,
            defaultValues: isset($data['defaultValues']) ? DefaultValues::fromArray($data['defaultValues']) : null,
        );
    }
}
