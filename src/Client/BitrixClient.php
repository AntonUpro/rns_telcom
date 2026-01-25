<?php

declare(strict_types=1);

namespace App\Client;

use App\Dto\Bitrix\DataFromBitrix;

class BitrixClient
{
    public function __construct()
    {
    }

    public function getDataByObjectCode(string $objectCode): DataFromBitrix
    {
        return new DataFromBitrix(
            stationNumber: "Номер базовой станции",
            region: "Регион",
            locality: 'Локация из битрикс',
            customer: 'Заказчик',
            amsType: 'Тип АМС',
            amsHeight: '10',
            inspectionDate: new \DateTimeImmutable(),
        );
    }
}
