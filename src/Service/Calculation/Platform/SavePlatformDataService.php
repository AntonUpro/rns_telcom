<?php

declare(strict_types=1);

namespace App\Service\Calculation\Platform;

use App\Dto\Calculation\Platform\Element;
use App\Dto\Calculation\Platform\PlatformSaveDataDto;
use App\Entity\Platform;
use App\Entity\PlatformSection;
use App\Enum\Pillar\PlatformSectionTypeEnum;
use App\Exception\NotFoundException;
use App\Repository\CalculationRepository;
use App\Repository\PlatformSectionsRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Throwable;

class SavePlatformDataService
{
    public function __construct(
        private readonly CalculationRepository $calculationRepository,
        private readonly PlatformSectionsRepository $platformSectionsRepository,
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

            $platform = $calculation->getPlatform();
            if (! $platform) {
                $platform = new Platform();
                $platform->setCalculation($calculation);
            }

            $platform->setMountingHeightStrut($platformData->totalData->mountHeightStrut)
                ->setMountingHeight($platformData->totalData->mountHeightPlatform)
                ->setFacetsCount($platformData->totalData->facetsCount)
                ->setUpdatedAt(new DateTimeImmutable());

            $this->entityManager->persist($platform);

            $existPlatformSectionByNumber = [];
            $existNumberSections = [];
            foreach ($platform->getSections() ?? [] as $platformSection) {
                $existPlatformSectionByNumber[$platformSection->getNumberSection()] = $platformSection;
                $existNumberSections[] = $platformSection->getNumberSection();
            }

            $requestPlatformSectionsByNumber = [];
            $requestSectionNumbers = [];
            foreach ($platformData->sections ?? [] as $key => $section) {
                $requestPlatformSectionsByNumber[$key + 1] = $section;
                $requestSectionNumbers[] = $key + 1;
            }

            if ($platformData->strut) {
                $requestSectionNumbers[] = 0;
                $sectionEntity = $existPlatformSectionByNumber[0] ?? new PlatformSection();

                $platformSectionStrut = $sectionEntity
                    ->setPlatform($platform)
                    ->setTypeSection(PlatformSectionTypeEnum::STRUT->value)
                    ->setNumberSection(0)
                    ->setHeight($platformData->strut->height)
                    ->setWidthBottom($platformData->strut->widthBottom)
                    ->setWidthTop($platformData->strut->widthTop)
                    ->setMountHeightBottom($platformData->totalData->mountHeightStrut)
                    ->setMountHeightTop($platformData->totalData->mountHeightPlatform)
                    ->setElements(array_map(fn(Element $element): array => $element->toArray(), $platformData->strut->elements))
                    ->setUpdatedAt(new DateTimeImmutable());

                $this->entityManager->persist($platformSectionStrut);
            }

            $mountHeightBottomSection = $platformData->totalData->mountHeightPlatform;
            foreach ($requestPlatformSectionsByNumber as $key => $section) {
                $sectionEntity = $existPlatformSectionByNumber[$key] ?? new PlatformSection();

                $platformSection = $sectionEntity
                    ->setPlatform($platform)
                    ->setTypeSection(PlatformSectionTypeEnum::SECTION->value)
                    ->setNumberSection($key)
                    ->setHeight($section->height)
                    ->setWidthBottom($section->widthBottom)
                    ->setWidthTop($section->widthTop)
                    ->setMountHeightBottom($mountHeightBottomSection)
                    ->setMountHeightTop($mountHeightBottomSection + $section->height)
                    ->setElements(array_map(fn(Element $element): array => $element->toArray(), $section->elements))
                    ->setUpdatedAt(new  DateTimeImmutable());

                $this->entityManager->persist($platformSection);

                $mountHeightBottomSection += $section->height;
            }

            $deleteSectionIds = array_diff($existNumberSections, $requestSectionNumbers);

            if ($deleteSectionIds && $platform->getId()) {
                $this->platformSectionsRepository->deleteSectionByNumberAndCalculationId($deleteSectionIds, $platform);
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
