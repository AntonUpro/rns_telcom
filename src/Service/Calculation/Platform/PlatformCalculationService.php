<?php

declare(strict_types=1);

namespace App\Service\Calculation\Platform;

use App\Dto\Calculation\Platform\TotalPlatformCalculationDto;
use App\Exception\NotFoundException;
use App\Repository\CalculationRepository;
use App\Repository\PlatformSectionsRepository;
use App\Service\Calculation\Platform\Calculator\SectionCalculator;

final readonly class PlatformCalculationService
{
    public function __construct(
        private CalculationRepository $calculationRepository,
        private PlatformSectionsRepository $platformSectionsRepository,
    ) {
    }

    public function calculatePlatform(int $platformId): TotalPlatformCalculationDto
    {
        $calculation = $this->calculationRepository->findById($platformId);
        if (! $calculation) {
            throw new NotFoundException(sprintf("Not found calculation %d", $platformId));
        }

        if (! $calculation->getPlatform()) {
            throw new NotFoundException(sprintf("Not found pillar platform for calculation %d", $platformId));
        }

        $platform = $calculation->getPlatform();
        if (! $platform) {
            throw new NotFoundException(sprintf("Not found pillar platform %d", $platformId));
        }

        $calculateSections = new TotalPlatformCalculationDto();
        foreach ($this->platformSectionsRepository->getSectionsByPlatformId($platform) as $section) {
            $calculateSections->add((new SectionCalculator(
                $calculation->getCalculationData()->getWindRegion(),
                $calculation->getCalculationData()->getTerrainType(),
                $section,
            ))->calculate());
        }

        return $calculateSections;
    }
}
