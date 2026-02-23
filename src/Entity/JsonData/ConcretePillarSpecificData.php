<?php

declare(strict_types=1);

namespace App\Entity\JsonData;

use App\Enum\Pillar\PillarEnum;

final class ConcretePillarSpecificData extends AbstractJsonData
{
    public function __construct(
        public readonly ?string $pillarStamp,
        public readonly float $markBottom,
        public readonly bool $strengtheningExist,
    ) {
    }

    public function toArray(): array
    {
        return [
            'pillarStamp' => $this->pillarStamp,
            'strengtheningExist' => $this->strengtheningExist,
        ];
    }

    public static function fromArray(array $data): static
    {
        return new static(
            pillarStamp: $data['pillarStamp'],
            markBottom: $data['markBottom'] ?? 0,
            strengtheningExist: $data['strengtheningExist'],
        );
    }

    public function toEnumPillar(): PillarEnum
    {
        $enum = PillarEnum::tryFrom($this->pillarStamp);
        if (! $enum) {
            throw new \InvalidArgumentException('Невалидная марка столба');
        }
        return $enum;
    }
}
