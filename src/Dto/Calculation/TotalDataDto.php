<?php

declare(strict_types=1);

namespace App\Dto\Calculation;

use Symfony\Component\Validator\Constraints as Assert;

class TotalDataDto
{
    public function __construct(
        #[Assert\Length(max: 50)]
        public ?string $objectCode = null,

        #[Assert\Length(max: 50)]
        public ?string $stationNumber = null,

        #[Assert\Length(max: 100)]
        public ?string $region = null,

        #[Assert\Length(max: 100)]
        public ?string $locality = null,

        public ?int $customer = null,

        #[Assert\Length(max: 100)]
        public ?string $amsType = null,

        #[Assert\Type('numeric')]
        #[Assert\Range(min: 0, max: 1000)]
        public ?float $amsHeight = null,

        #[Assert\DateTime]
        public ?string $inspectionDate = null,

        #[Assert\Type('numeric')]
        #[Assert\Range(min: -90, max: 90)]
        public ?float $latitude = null,

        #[Assert\Type('numeric')]
        #[Assert\Range(min: -180, max: 180)]
        public ?float $longitude = null,

        #[Assert\Type('bool')]
        public bool $surveyPerformed = false,
    ) {}

    public function toArray(): array
    {
        return [
            'objectCode' => $this->objectCode,
            'stationNumber' => $this->stationNumber,
            'region' => $this->region,
            'locality' => $this->locality,
            'customer' => $this->customer,
            'amsType' => $this->amsType,
            'amsHeight' => $this->amsHeight,
            'inspectionDate' => $this->inspectionDate,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'surveyPerformed' => $this->surveyPerformed,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            objectCode: $data['objectCode'] ?? null,
            stationNumber: $data['stationNumber'] ?? null,
            region: $data['region'] ?? null,
            locality: $data['locality'] ?? null,
            customer: isset($data['customer']) ? (int) $data['customer'] : null,
            amsType: $data['amsType'] ?? null,
            amsHeight: isset($data['amsHeight']) ? (float) $data['amsHeight'] : null,
            inspectionDate: $data['inspectionDate'] ?? null,
            latitude: isset($data['latitude']) ? (float) $data['latitude'] : null,
            longitude: isset($data['longitude']) ? (float) $data['longitude'] : null,
            surveyPerformed: isset($data['surveyPerformed'])
                ? filter_var($data['surveyPerformed'], FILTER_VALIDATE_BOOLEAN)
                : false,
        );
    }

    public function isEmpty(): bool
    {
        $array = $this->toArray();
        // Игнорируем булево поле surveyPerformed при проверке на пустоту
        unset($array['surveyPerformed']);
        return empty(array_filter($array, fn($value) => $value !== null));
    }
}
