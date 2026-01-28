<?php

declare(strict_types=1);

namespace App\Entity\JsonData;

final class ConcretePillarSpecificData extends AbstractJsonData
{
    public function __construct(
        public readonly ?string $pillarStamp,
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
            strengtheningExist: $data['strengtheningExist'],
        );
    }
}
