<?php

declare(strict_types=1);

namespace App\Entity\JsonData;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\JsonbType;

final class ConcretePillarSpecificDataType extends JsonbType
{
    public const NAME = 'concrete_pillar_data';

    public function getName(): string
    {
        return self::NAME;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?ConcretePillarSpecificData
    {
        $data = parent::convertToPHPValue($value, $platform);
        return $data ? ConcretePillarSpecificData::fromArray($data) : null;
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value instanceof ConcretePillarSpecificData) {
            $value = $value->toArray();
        }

        return parent::convertToDatabaseValue($value, $platform);
    }
}
