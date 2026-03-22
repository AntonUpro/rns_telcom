<?php

declare(strict_types=1);

namespace App\Dto\Calculation\Pillar\Platform;

class PlatformSaveDataDto
{
    /**
     * @param PlatformSection[] $sections
     * @param PlatformSection $strut
     */
    public function __construct(
        public int $calculationId,
        public TotalDataPlatform $totalData,
        public array $sections,
        public ?PlatformSection $strut,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            calculationId: $data['calculationId'],
            totalData: TotalDataPlatform::fromArray($data['totalData']),
            sections: array_map(fn (array $section) => PlatformSection::fromArray($section), $data['sections']),
            strut: PlatformSection::fromArray($data['strut']) ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'calculationId' => $this->calculationId,
            'totalData' => $this->totalData->toArray(),
            'sections' => array_map(fn (PlatformSection $section) => $section->toArray(), $this->sections),
            'strut' => $this->strut?->toArray(),
        ];
    }
}
