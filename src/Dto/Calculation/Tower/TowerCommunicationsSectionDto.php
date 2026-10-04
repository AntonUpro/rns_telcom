<?php

declare(strict_types=1);

namespace App\Dto\Calculation\Tower;

use App\Dto\Calculation\Pillar\PartSectionDto;

/**
 * Ветровая нагрузка на коммуникации одной секции башни:
 * кабельную трассу, кабельные полки (кабельрост) и лестницу.
 */
final readonly class TowerCommunicationsSectionDto
{
    public function __construct(
        public int $sectionNumber,
        /** Отметка верха секции, мм */
        public float $topMark,
        public float $kze,
        /** Нормативное ветровое давление Wo, кг/м² */
        public float $windPress,
        public float $securityCoefficient,
        public ?PartSectionDto $cable,
        public ?PartSectionDto $cableChannel,
        public ?PartSectionDto $ladder,
    ) {
    }

    /** Суммарная ветровая нагрузка на коммуникации секции, кг */
    public function totalPress(): float
    {
        return ($this->cable?->press ?? 0)
            + ($this->cableChannel?->press ?? 0)
            + ($this->ladder?->press ?? 0);
    }
}
