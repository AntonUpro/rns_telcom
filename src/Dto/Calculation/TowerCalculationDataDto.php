<?php

declare(strict_types=1);

namespace App\Dto\Calculation;

use Symfony\Component\Validator\Constraints as Assert;

class TowerCalculationDataDto
{
    public function __construct(
        public int $calculationId,

        #[Assert\Valid]
        public ?TotalDataDto $totalData = null,

        #[Assert\Valid]
        public ?ClimateDataDto $climateData = null,

        #[Assert\Valid]
        public ?TowerDataDto $towerData = null,

        public ?array $defaultValues = null,
    ) {
        $this->totalData ??= new TotalDataDto();
        $this->climateData ??= new ClimateDataDto();
        $this->towerData ??= new TowerDataDto();
        $this->defaultValues ??= [];
    }

    public function toArray(): array
    {
        return [
            'totalData' => $this->totalData?->toArray(),
            'climateData' => $this->climateData?->toArray(),
            'towerData' => $this->towerData?->toArray(),
            'defaultValues' => $this->defaultValues,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            calculationId: $data['calculationId'] ?? 0,
            totalData: isset($data['totalData']) ? TotalDataDto::fromArray($data['totalData']) : null,
            climateData: isset($data['climateData']) ? ClimateDataDto::fromArray($data['climateData']) : null,
            towerData: isset($data['towerData']) ? TowerDataDto::fromArray($data['towerData']) : null,
            defaultValues: $data['defaultValues'] ?? null,
        );
    }

    public static function fromRequest(array $formData): self
    {
        return new self(
            calculationId: $formData['calculationId'] ?? 0,
            totalData: new TotalDataDto(
                objectCode: $formData['totalData']['objectCode'] ?? null,
                stationNumber: $formData['totalData']['stationNumber'] ?? null,
                region: $formData['totalData']['region'] ?? null,
                locality: $formData['totalData']['locality'] ?? null,
                customer: isset($formData['totalData']['customer']) ? (int) $formData['totalData']['customer'] : null,
                amsType: $formData['totalData']['amsType'] ?? null,
                amsHeight: isset($formData['totalData']['amsHeight']) ? (float) $formData['totalData']['amsHeight'] : null,
                inspectionDate: $formData['totalData']['inspectionDate'] ?? null,
                latitude: isset($formData['totalData']['latitude']) ? (float) $formData['totalData']['latitude'] : null,
                longitude: isset($formData['totalData']['longitude']) ? (float) $formData['totalData']['longitude'] : null,
                surveyPerformed: isset($formData['totalData']['surveyPerformed'])
                    ? filter_var($formData['totalData']['surveyPerformed'], FILTER_VALIDATE_BOOLEAN)
                    : false,
            ),
            climateData: new ClimateDataDto(
                windRegion: $formData['climateData']['windRegion'] ?? null,
                terrainType: $formData['climateData']['terrainType'] ?? null,
                snowRegion: $formData['climateData']['snowRegion'] ?? null,
                iceRegion: $formData['climateData']['iceRegion'] ?? null,
            ),
            towerData: new TowerDataDto(
                markBottom: isset($formData['towerData']['markBottom']) ? (float) $formData['towerData']['markBottom'] : null,
                towerHeight: isset($formData['towerData']['towerHeight']) ? (float) $formData['towerData']['towerHeight'] : null,
                facetsCount: isset($formData['towerData']['facetsCount']) ? (int) $formData['towerData']['facetsCount'] : 3,
            ),
            defaultValues: $formData['defaultValues'] ?? null,
        );
    }

    public function isEmpty(): bool
    {
        return $this->totalData?->isEmpty()
            && $this->climateData?->isEmpty()
            && $this->towerData?->isEmpty();
    }
}
