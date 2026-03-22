<?php

declare(strict_types=1);

namespace App\Dto\Calculation\Pillar\Platform;

final readonly class TotalDataPlatform
{
    public function __construct(
        public int $mountHeightStrut,
        public int $mountHeightPlatform,
        public int $facetsCount,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['mountHeightStrut'],
            $data['mountHeightPlatform'],
            $data['facetsCount'],
        );
    }

    public function toArray(): array
    {
        return [
            'mountHeightStrut' => $this->mountHeightStrut,
            'mountHeightPlatform' => $this->mountHeightPlatform,
            'facetsCount' => $this->facetsCount,
        ];
    }
}
