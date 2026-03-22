<?php

declare(strict_types=1);

namespace App\Service\Calculation\Pillar\Platform;

use App\Dto\Calculation\Pillar\Platform\PlatformSaveDataDto;
use App\Entity\PillarPlatform;
use App\Entity\PillarPlatformSection;
use App\Enum\Pillar\PlatformSectionTypeEnum;
use App\Exception\NotFoundException;
use App\Repository\CalculationRepository;
use App\Repository\PillarPlatformRepository;
use App\Repository\PillarPlatformSectionsRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Throwable;

class SavePlatformDataService
{
    public function __construct(
        private readonly CalculationRepository $calculationRepository,
        private readonly PillarPlatformSectionsRepository $pillarPlatformSectionsRepository,
        private readonly PillarPlatformRepository $pillarPlatformRepository,
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger,
    ) {
    }

    public function savePlatformData(PlatformSaveDataDto $platformData): void
    {
        try {
            $this->entityManager->beginTransaction();

            $calculation = $this->calculationRepository->findById($platformData->calculationId);

            if (! $calculation) {
                throw new NotFoundException(sprintf('Calculation with id %s not found', $platformData->calculationId));
            }

            $pillarPlatform = $calculation->getPillarPlatform();
            if (! $pillarPlatform) {
                $pillarPlatform = new PillarPlatform();
                $pillarPlatform->setCalculation($calculation);
            }

            $pillarPlatform->setMountingHeightStrut($platformData->totalData->mountHeightStrut)
                ->setMountingHeight($platformData->totalData->mountHeightPlatform)
                ->setFacetsCount($platformData->totalData->facetsCount)
                ->setUpdatedAt(new DateTimeImmutable());

            $this->entityManager->persist($pillarPlatform);

            $existPlatformSectionByNumber = [];
            $existNumberSections = [];
            foreach ($pillarPlatform->getSections() ?? [] as $pillarPlatformSection) {
                $existPlatformSectionByNumber[$pillarPlatformSection->getNumberSection()] = $pillarPlatformSection;
                $existNumberSections[] = $pillarPlatformSection->getNumberSection();
            }

            $requestPlatformSectionsByNumber = [];
            $requestSectionNumbers = [];
            foreach ($platformData->sections ?? [] as $key => $section) {
                $requestPlatformSectionsByNumber[$key + 1] = $section;
                $requestSectionNumbers[] = $key + 1;
            }

            if ($platformData->strut) {
                $requestSectionNumbers[] = 0;
                $sectionEntity = $existPlatformSectionByNumber[$key] ?? new PillarPlatformSection();

                $pillarPlatformSection = $sectionEntity
                    ->setPillarPlatform($pillarPlatform)
                    ->setTypeSection(PlatformSectionTypeEnum::STRUT->value)
                    ->setNumberSection(0)
                    ->setHeight($platformData->strut->height)
                    ->setWidthBottom($platformData->strut->widthBottom)
                    ->setWidthTop($platformData->strut->widthTop)
                    ->setMountHeightBottom($platformData->totalData->mountHeightStrut)
                    ->setMountHeightTop($platformData->totalData->mountHeightPlatform)
                    ->setElements($platformData->strut->elements)
                    ->setUpdatedAt(new DateTimeImmutable());

                $this->entityManager->persist($pillarPlatformSection);
            }

            $mountHeightBottomSection = $platformData->totalData->mountHeightPlatform;
            foreach ($requestPlatformSectionsByNumber as $key => $section) {
                $sectionEntity = $existPlatformSectionByNumber[$key] ?? new PillarPlatformSection();

                $pillarPlatformSection = $sectionEntity
                    ->setPillarPlatform($pillarPlatform)
                    ->setTypeSection(PlatformSectionTypeEnum::SECTION->value)
                    ->setNumberSection($key)
                    ->setHeight($section->height)
                    ->setWidthBottom($section->widthBottom)
                    ->setWidthTop($section->widthTop)
                    ->setMountHeightBottom($mountHeightBottomSection)
                    ->setMountHeightTop($mountHeightBottomSection + $section->height)
                    ->setElements($section->elements)
                    ->setUpdatedAt(new  DateTimeImmutable());

                $this->entityManager->persist($pillarPlatformSection);

                $mountHeightBottomSection += $section->height;
            }

            $deleteSectionIds = array_diff($existNumberSections, $requestSectionNumbers);

            if ($deleteSectionIds && $pillarPlatform->getId()) {
                $this->pillarPlatformSectionsRepository->deleteSectionByNumberAndCalculationId($deleteSectionIds, $pillarPlatform->getId());
            }

            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (Throwable $exception) {
            $this->entityManager->rollback();
            $this->logger->error($exception->getMessage());
            throw $exception;
        }
    }
}
