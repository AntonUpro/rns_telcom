<?php

declare(strict_types=1);

namespace App\Service\Calculation\TotalLoad;

use App\Dto\Calculation\TotalLoad\EquipmentHeightTotalLoadDto;
use App\Dto\Calculation\TotalLoad\PillarSectionTotalLoadDto;
use App\Dto\Calculation\TotalLoad\PlatformSectionTotalLoadDto;
use App\Dto\Calculation\TotalLoad\TotalLoadResponseDto;
use App\Dto\DefaultConstant;
use App\Entity\CalculationData;
use App\Entity\PillarPlatformSection;
use App\Enum\Pillar\PlatformSectionTypeEnum;
use App\Exception\NotFoundException;
use App\Repository\CalculationRepository;
use App\Service\Calculation\Equipment\CalculationWindEquipmentService;
use App\Service\Calculation\Pillar\Pillar\PillarWindLoadCalculationService;
use App\Service\Calculation\PillarPlatform\PillarPlatformCalculationService;

/**
 * Собирает суммарную нагрузку для таба 5:
 *   - Таблица 1: нагрузка по секциям ствола + коммуникаций
 *   - Таблица 2: нагрузка на площадку и надстройку
 *   - Таблица 3: нагрузка на оборудование, сгруппированная по высотной отметке
 */
final readonly class TotalLoadService
{
    /**
     * Cx для элементов площадки (пояса, раскосы) — трубчатые профили.
     * TODO: уточнить значение Cx у проектировщика (возможно зависит от типа сечения элемента).
     */
    private const PLATFORM_ELEMENT_CX = 1.4;

    public function __construct(
        private CalculationRepository $calculationRepository,
        private PillarWindLoadCalculationService $pillarWindLoadCalculationService,
        private CalculationWindEquipmentService $calculationWindEquipmentService,
        private PillarPlatformCalculationService $pillarPlatformCalculationService,
    ) {
    }

    /**
     * @throws NotFoundException
     */
    public function getTotalLoad(int $calculationId): TotalLoadResponseDto
    {
        $calculation = $this->calculationRepository->findById($calculationId);
        if ($calculation === null) {
            throw new NotFoundException(sprintf('Расчет с ID %d не найден', $calculationId));
        }

        $calculationData = $calculation->getCalculationData();
        if ($calculationData === null) {
            throw new NotFoundException(sprintf('Данные расчета с ID %d не найдены', $calculationId));
        }

        $response = new TotalLoadResponseDto();

        $this->fillPillarSections($response, $calculationId);
        $this->fillPlatformSections($response, $calculationId);
        $this->fillEquipmentHeights($response, $calculationId);

        return $response;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Таблица 1: нагрузка по секциям ствола + коммуникаций
    // ─────────────────────────────────────────────────────────────────────────

    private function fillPillarSections(TotalLoadResponseDto $response, int $calculationId): void
    {
        $sectionCollection = $this->pillarWindLoadCalculationService->calculate($calculationId);

        foreach ($sectionCollection->getSections() as $pillarDto) {
            $meta = $pillarDto->totalCalculationDataDto;

            // Суммируем давление по всем составляющим секции (кг)
            $totalLoadKgf = $pillarDto->pillarPart->press
                + ($pillarDto->cablePart?->press ?? 0)
                + ($pillarDto->cableChanelPart?->press ?? 0)
                + ($pillarDto->ladderPart?->press ?? 0);

            $heightM = $meta->heightSection > 0 ? $meta->heightSection / 1000 : 0;

            $response->addPillarSection(new PillarSectionTotalLoadDto(
                sectionNumber: $meta->numberSection,
                topHeight: $meta->topHeight,        // мм
                sectionHeight: $meta->heightSection,    // мм
                totalLoad: $totalLoadKgf,
                loadPerLinearMeter: $heightM > 0 ? $totalLoadKgf / $heightM : 0,
            ));
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Таблица 2: нагрузка на площадку и надстройку
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * @param PillarPlatformSection[] $platformSections
     */
    private function fillPlatformSections(
        TotalLoadResponseDto $response,
        int $calculationId,
    ): void {
        $totalLoad = $this->pillarPlatformCalculationService->calculatePillarPlatform($calculationId);

        foreach ($totalLoad->platformSections as $section) {
            $isStrut = $section->type === PlatformSectionTypeEnum::STRUT;
            $label = $isStrut ? 'Подкосы' : (string)$section->numberSection;
            $topHeight = (float)($section->mountingHeightSection + $section->heightSection );   // мм

            $response->addPlatformSection(new PlatformSectionTotalLoadDto(
                label: $label,
                isStrut: $isStrut,
                topHeight: $topHeight,
                height: $section->heightSection,
                totalLoad: $section->press,
                loadPerLinearMeterPerBelt: $section->press / $section->heightSection * 1000,
            ));
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Таблица 3: нагрузка на оборудование по высотным отметкам
    // ─────────────────────────────────────────────────────────────────────────

    private function fillEquipmentHeights(
        TotalLoadResponseDto $response,
        int $calculationId,
    ): void {
        try {
            $equipmentResults = $this->calculationWindEquipmentService->calculate($calculationId);
        } catch (NotFoundException) {
            return;
        }

        // Группируем нагрузки по высотной группе (heightGroup задаётся пользователем)
        /** @var array<int, array{heightMark: float, totalLoad: float}> $byGroup */
        $byGroup = [];

        foreach ($equipmentResults as $byType) {
            foreach ($byType as $results) {
                foreach ($results as $result) {
                    $group = $result->heightGroup;
                    $mountHeightMm = $result->monthHeight * 1000;

                    if (! isset($byGroup[$group])) {
                        $byGroup[$group] = [
                            'heightGroup' => $group,
                            'heightMark' => $mountHeightMm,
                            'totalLoad' => 0.0,
                        ];
                    } else {
                        // Берём наибольшую отметку подвеса в группе как представительную
                        $byGroup[$group]['heightMark'] = max($byGroup[$group]['heightMark'], $mountHeightMm);
                    }

                    $byGroup[$group]['totalLoad'] += $result->pressOnOneEquipment * $result->quantity;
                }
            }
        }

        if ($byGroup === []) {
            return;
        }

        // Сортируем по номеру высотной группы
        ksort($byGroup);

        // Высота интервала — расстояние от предыдущей отметки до текущей
        $prevHeightMm = 0.0;
        foreach ($byGroup as $group => $row) {
            $intervalMm = $row['heightMark'] - $prevHeightMm;
            $prevHeightMm = $row['heightMark'];

            $response->addEquipmentHeight(new EquipmentHeightTotalLoadDto(
                heightGroup: $group,
                height: $row['heightMark'],
                totalLoad: $row['totalLoad'],
            ));
        }
    }
}
