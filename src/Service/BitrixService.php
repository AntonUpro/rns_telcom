<?php

declare(strict_types=1);

namespace App\Service;

use App\Client\BitrixClient;
use App\Dto\Bitrix\DataFromBitrix;

final readonly class BitrixService
{
    public function __construct(
        private BitrixClient $bitrixClient,
    ) {
    }

    public function loadDataFromBitrix(string $objectCode): DataFromBitrix
    {
        return $this->bitrixClient->getDataByObjectCode($objectCode);
    }
}
