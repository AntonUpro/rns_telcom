<?php

declare(strict_types=1);

namespace App\Dto\Bitrix;

use DateTimeImmutable;
use JsonSerializable;

final readonly class DataFromBitrix implements JsonSerializable
{
    public function __construct(
        public string $stationNumber,
        public string $region,
        public string $locality,
        public string $customer,
        public string $amsType,
        public string $amsHeight,
        public DateTimeImmutable $inspectionDate,
    ) {
    }

    public function jsonSerialize(): array
    {
        return [
            'stationNumber' => $this->stationNumber,
            'region' => $this->region,
            'locality' => $this->locality,
            'customer' => $this->customer,
            'amsType' => $this->amsType,
            'amsHeight' => $this->amsHeight,
            'inspectionDate' => $this->inspectionDate->format('Y-m-d'),
        ];
    }
}
