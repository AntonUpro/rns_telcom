<?php

declare(strict_types=1);

namespace App\Service\Calculation\Platform;

use App\Dto\Calculation\Platform\Element;
use App\Dto\Calculation\Platform\PlatformSaveDataDto;
use App\Dto\Calculation\Platform\PlatformSection;
use App\Dto\Calculation\Platform\TotalDataPlatform;
use App\Enum\Pillar\ElementTypeEnum;
use App\Enum\Pillar\PlatformSectionTypeEnum;
use App\Enum\Pillar\SectionConstructTypeEnum;
use App\Exception\NotFoundException;
use App\Repository\CalculationRepository;
use App\Repository\PlatformSectionsRepository;

class GetPlatformDataService
{
    public function __construct(
        private readonly CalculationRepository $calculationRepository,
        private readonly PlatformSectionsRepository $platformSectionsRepository,
    ) {
    }

    public function getPlatformData(int $calculationId): ?PlatformSaveDataDto
    {
        $calculation = $this->calculationRepository->findById($calculationId);

        if (! $calculation) {
            throw new NotFoundException(sprintf('Calculation with id %s not found', $calculationId));
        }

        $platformData = $calculation->getPlatform();
        if (! $platformData) {
            $mountHeightPlatform = ($calculation->getCalculationData()?->getConcretePillarSpecificData()?->pillarHeight ?: 23) * 1000;
            $mountHeightStrut = $mountHeightPlatform - 1500;
            return new PlatformSaveDataDto(
                calculationId: $calculationId,
                totalData: new TotalDataPlatform(
                    mountHeightStrut: (int) $mountHeightStrut,
                    mountHeightPlatform: (int) $mountHeightPlatform,
                    facetsCount: 4,
                ),
                strut: null,
                sections: [],
            );
        }

        $sections = [];
        $strut = null;
        foreach ($this->platformSectionsRepository->getSectionsByPlatformId($platformData) as $platformDataSection) {
            if ($platformDataSection->isStrut()) {
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
                type: ElementTypeEnum::from($sectionElement['type']),
                sectionType: SectionConstructTypeEnum::from($sectionElement['sectionType']),
                widthElement: $sectionElement['widthElement'],
                lengthElement: $sectionElement['lengthElement'],
                countElement: $sectionElement['countElement'],
            );
        }

        return $elements;
    }
}
