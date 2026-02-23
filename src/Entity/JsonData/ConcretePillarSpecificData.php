<?php

declare(strict_types=1);

namespace App\Entity\JsonData;

use App\Entity\JsonData\Dto\Strengthening;
use App\Enum\Pillar\PillarEnum;
use InvalidArgumentException;

final class ConcretePillarSpecificData extends AbstractJsonData
{
    public function __construct(
        public readonly ?string $pillarStamp,
        public readonly float $markBottom,
        public readonly bool $strengtheningExist,
        public readonly ?Strengthening $strengthening,
    ) {
    }

    public function toArray(): array
    {
        return [
            'pillarStamp' => $this->pillarStamp,
            'markBottom' => $this->markBottom,
            'strengtheningExist' => $this->strengtheningExist,
            'strengthening' => $this->strengthening?->toArray(),
        ];
    }

    public static function fromArray(array $data): static
    {
        return new static(
            pillarStamp: $data['pillarStamp'],
            markBottom: $data['markBottom'] ?? 0,
            strengtheningExist: $data['strengtheningExist'],
            strengthening: isset($data['strengthening']) ? Strengthening::fromArray($data['strengthening']) : null,
        );
    }

    public function toEnumPillar(): PillarEnum
    {
        $enum = PillarEnum::tryFrom($this->pillarStamp);
        if (! $enum) {
            throw new InvalidArgumentException('Невалидная марка столба');
        }
        return $enum;
    }
}
