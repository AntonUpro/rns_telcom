<?php

declare(strict_types=1);

namespace App\Dto\Calculation\TotalLoad;

/**
 * Корневой DTO ответа для таба «Суммарная нагрузка».
 * Содержит три коллекции, соответствующие трём таблицам на фронтенде.
 */
final class TotalLoadResponseDto
{
    /** @var PillarSectionTotalLoadDto[] */
    private array $pillarSections = [];

    /** @var PlatformSectionTotalLoadDto[] */
    private array $platformSections = [];

    /** @var EquipmentHeightTotalLoadDto[] */
    private array $equipmentHeights = [];

    public function addPillarSection(PillarSectionTotalLoadDto $dto): void
    {
        $this->pillarSections[] = $dto;
    }

    public function addPlatformSection(PlatformSectionTotalLoadDto $dto): void
    {
        $this->platformSections[] = $dto;
    }

    public function addEquipmentHeight(EquipmentHeightTotalLoadDto $dto): void
    {
        $this->equipmentHeights[] = $dto;
    }

    public function toArray(): array
    {
        return [
            'pillarSections'   => array_map(static fn (PillarSectionTotalLoadDto $d) => $d->toArray(), $this->pillarSections),
            'platformSections' => array_map(static fn (PlatformSectionTotalLoadDto $d) => $d->toArray(), $this->platformSections),
            'equipmentHeights' => array_map(static fn (EquipmentHeightTotalLoadDto $d) => $d->toArray(), $this->equipmentHeights),
        ];
    }
}
