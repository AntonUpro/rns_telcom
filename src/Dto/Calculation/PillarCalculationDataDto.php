<?php

declare(strict_types=1);

namespace App\Dto\Calculation;

use Symfony\Component\Validator\Constraints as Assert;

class PillarCalculationDataDto
{
    public function __construct(
        public int $calculationId,

        #[Assert\Valid]
        public ?TotalDataDto $totalData = null,

        #[Assert\Valid]
        public ?ClimateDataDto $climateData = null,

        #[Assert\Valid]
        public ?PillarDataDto $pillarData = null,

        public ?array $defaultValues = null,
    ) {
        // Инициализируем вложенные DTO если они null
        $this->totalData ??= new TotalDataDto();
        $this->climateData ??= new ClimateDataDto();
        $this->pillarData ??= new PillarDataDto();
        $this->defaultValues ??= [];
    }

    public function toArray(): array
    {
        return [
            'totalData' => $this->totalData?->toArray(),
            'climateData' => $this->climateData?->toArray(),
            'pillarData' => $this->pillarData?->toArray(),
            'defaultValues' => $this->defaultValues,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            calculationId: $formData['calculationId'] ?? 0,
            totalData: isset($data['totalData']) ? TotalDataDto::fromArray($data['totalData']) : null,
            climateData: isset($data['climateData']) ? ClimateDataDto::fromArray($data['climateData']) : null,
            pillarData: isset($data['pillarData']) ? PillarDataDto::fromArray($data['pillarData']) : null,
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
                customer: $formData['totalData']['customer'] ?? null,
                amsType: $formData['totalData']['amsType'] ?? null,
                amsHeight: isset($formData['totalData']['amsHeight']) ? (float) $formData['totalData']['amsHeight'] : null,
                inspectionDate: $formData['totalData']['inspectionDate'] ?? null,
                latitude: isset($formData['totalData']['latitude']) ? (float) $formData['totalData']['latitude'] : null,
                longitude: isset($formData['totalData']['longitude']) ? (float) $formData['totalData']['longitude'] : null,
            ),
            climateData: new ClimateDataDto(
                windRegion: $formData['climateData']['windRegion'] ?? null,
                terrainType: $formData['climateData']['terrainType'] ?? null,
                snowRegion: $formData['climateData']['snowRegion'] ?? null,
                iceRegion: $formData['climateData']['iceRegion'] ?? null,
            ),
            pillarData: new PillarDataDto(
                pillarStamp: $formData['pillarData']['pillarStamp'] ?? null,
                strengtheningExist: isset($formData['pillarData']['strengtheningExist'])
                    ? filter_var($formData['pillarData']['strengtheningExist'], FILTER_VALIDATE_BOOLEAN)
                    : false,
                strengtheningGeometry: $formData['pillarData']['strengtheningGeometry'] ?? null,
                strengtheningWidth: isset($formData['pillarData']['strengtheningWidth'])
                    ? (float) $formData['pillarData']['strengtheningWidth']
                    : null,
                strengtheningHeight: isset($formData['pillarData']['strengtheningHeight'])
                    ? (float) $formData['pillarData']['strengtheningHeight']
                    : null,
                allowedMoment: isset($formData['pillarData']['allowedMoment'])
                    ? (float) $formData['pillarData']['allowedMoment']
                    : null,
            ),
            defaultValues: $formData['defaultValues'] ?? null,
        );
    }

    public function isEmpty(): bool
    {
        return $this->totalData?->isEmpty()
            && $this->climateData?->isEmpty()
            && $this->pillarData?->isEmpty();
    }
}
