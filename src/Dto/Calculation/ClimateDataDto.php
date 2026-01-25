<?php

declare(strict_types=1);

namespace App\Dto\Calculation;

use App\Enum\CalculationData\IcingRegionEnum;
use App\Enum\CalculationData\SnowRegionEnum;
use App\Enum\CalculationData\TerrainTypeEnum;
use App\Enum\CalculationData\WindRegionEnum;
use Symfony\Component\Validator\Constraints as Assert;

class ClimateDataDto
{
    public function __construct(
        #[Assert\Choice(callback: [WindRegionEnum::class, 'values'])]
        public ?string $windRegion = null,

        #[Assert\Choice(callback: [TerrainTypeEnum::class, 'values'])]
        public ?string $terrainType = null,

        #[Assert\Choice(callback: [SnowRegionEnum::class, 'values'])]
        public ?string $snowRegion = null,

        #[Assert\Choice(callback: [IcingRegionEnum::class, 'values'])]
        public ?string $iceRegion = null,
    ) {}

    public function toArray(): array
    {
        return [
            'windRegion' => $this->windRegion,
            'terrainType' => $this->terrainType,
            'snowRegion' => $this->snowRegion,
            'iceRegion' => $this->iceRegion,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            windRegion: $data['windRegion'] ?? null,
            terrainType: $data['terrainType'] ?? null,
            snowRegion: $data['snowRegion'] ?? null,
            iceRegion: $data['iceRegion'] ?? null,
        );
    }

    public function isEmpty(): bool
    {
        return empty(array_filter($this->toArray(), fn($value) => $value !== null));
    }

    // Вспомогательные методы для расчетов
    public function getWindPressure(): ?float
    {
        if (!$this->windRegion) {
            return null;
        }

        try {
            $region = WindRegionEnum::from($this->windRegion);
            return $region->pressure();
        } catch (\ValueError) {
            return null;
        }
    }

    public function getSnowLoad(): ?float
    {
        if (!$this->snowRegion) {
            return null;
        }

        try {
            $region = SnowRegionEnum::from($this->snowRegion);
            return $region->snowLoad();
        } catch (\ValueError) {
            return null;
        }
    }

    public function getTerrainCoefficient(float $height): ?float
    {
        if (!$this->terrainType) {
            return null;
        }

        try {
            $terrain = TerrainTypeEnum::from($this->terrainType);
            return $terrain->roughnessCoefficient($height);
        } catch (\ValueError) {
            return null;
        }
    }
}
