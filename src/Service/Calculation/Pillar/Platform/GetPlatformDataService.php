<?php

declare(strict_types=1);

namespace App\Service\Calculation\Pillar\Platform;

use App\Dto\Calculation\Pillar\Platform\Element;
use App\Dto\Calculation\Pillar\Platform\PlatformSaveDataDto;
use App\Dto\Calculation\Pillar\Platform\PlatformSection;
use App\Dto\Calculation\Pillar\Platform\TotalDataPlatform;
use App\Enum\Pillar\PlatformSectionTypeEnum;
use App\Exception\NotFoundException;
use App\Repository\CalculationRepository;
use App\Repository\PillarPlatformSectionsRepository;

class GetPlatformDataService
{
    public function __construct(
        private readonly CalculationRepository $calculationRepository,
        private readonly PillarPlatformSectionsRepository $pillarPlatformSectionsRepository,
    ) {
    }

    public function getPlatformData(int $calculationId): PlatformSaveDataDto
    {
        $calculation = $this->calculationRepository->findById($calculationId);

        if (! $calculation) {
            throw new NotFoundException(sprintf('Calculation with id %s not found', $calculationId));
        }

        $platformData = $calculation->getPillarPlatform();
        if (! $platformData) {
            throw new NotFoundException(sprintf('Not found pillar platform data for calculation with id %s', $calculationId));
        }

        $sections = [];
        $strut = null;
        foreach ($this->pillarPlatformSectionsRepository->getPlatformSectionsByPillarPlatformId($platformData) as $platformDataSection) {
            if ($platformDataSection->getTypeSection() === PlatformSectionTypeEnum::STRUT->value) {
                $strut = new PlatformSection(
                    id: $platformDataSection->getId(),
                    height: $platformDataSection->getHeight(),
                    widthBottom: $platformDataSection->getWidthBottom(),
                    widthTop: $platformDataSection->getWidthTop(),
                    elements: $this->buildElements($platformDataSection->getElements()),
                );
            } elseif ($platformDataSection->getTypeSection() === PlatformSectionTypeEnum::SECTION->value) {
                $sections[] = new PlatformSection(
                    id: $platformDataSection->getId(),
                    height: $platformDataSection->getHeight(),
                    widthBottom: $platformDataSection->getWidthBottom(),
                    widthTop: $platformDataSection->getWidthTop(),
                    elements: $this->buildElements($platformDataSection->getElements()),
                );
            }
        }

        return new PlatformSaveDataDto(
            calculationId: $calculationId,
            totalData: new TotalDataPlatform(
                mountHeightStrut: (int) $platformData->getMountingHeightStrut(),
                mountHeightPlatform: (int) $platformData->getMountingHeight(),
                facetsCount: $platformData->getFacetsCount(),
            ),
            strut: $strut,
            sections: $sections,
        );
    }

    /**
     * @return Element[]
     */
    private function buildElements(array $sectionElements): array
    {
        $elements = [];
        foreach ($sectionElements as $sectionElement) {
            $elements[] = new Element(
                type: $sectionElement['type'],
                sectionType: $sectionElement['sectionType'],
                widthElement: $sectionElement['widthElement'],
                lengthElement: $sectionElement['lengthElement'],
                countElement: $sectionElement['countElement'],
            );
        }

        return $elements;
    }
}
