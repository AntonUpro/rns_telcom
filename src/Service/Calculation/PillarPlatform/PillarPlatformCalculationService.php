<?php

declare(strict_types=1);

namespace App\Service\Calculation\PillarPlatform;

use App\Dto\Calculation\PillarPlatform\TotalPillarPlatformCalculationDto;
use App\Exception\NotFoundException;
use App\Repository\CalculationRepository;
use App\Repository\PillarPlatformSectionsRepository;
use App\Service\Calculation\PillarPlatform\Calculator\SectionCalculator;

final readonly class PillarPlatformCalculationService
{
    public function __construct(
        private CalculationRepository $calculationRepository,
        private PillarPlatformSectionsRepository $pillarPlatformSectionsRepository,
    ) {
    }

    public function calculatePillarPlatform(int $platformId): TotalPillarPlatformCalculationDto
    {
        $calculation = $this->calculationRepository->findById($platformId);
        if (! $calculation) {
            throw new NotFoundException(sprintf("Not found calculation %d", $platformId));
        }

        if (! $calculation->getPillarPlatform()) {
            throw new NotFoundException(sprintf("Not found pillar platform for calculation %d", $platformId));
        }

        $pillarPlatform = $calculation->getPillarPlatform();
        if (! $pillarPlatform) {
            throw new NotFoundException(sprintf("Not found pillar platform %d", $platformId));
        }

        $calculateSections = new TotalPillarPlatformCalculationDto();
        foreach ($this->pillarPlatformSectionsRepository->getPlatformSectionsByPillarPlatformId($pillarPlatform) as $section) {
            $calculateSections->add((new SectionCalculator(
                $calculation->getCalculationData()->getWindRegion(),
                $calculation->getCalculationData()->getTerrainType(),
                $section,
            ))->calculate());
        }

        return $calculateSections;
    }
}
