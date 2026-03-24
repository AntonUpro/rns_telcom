<?php

declare(strict_types=1);

namespace App\Service\Calculation\TotalLoad;

use App\Dto\Calculation\Equipment\Calculate\EquipmentCalculator;
use App\Dto\Calculation\Equipment\Calculate\RectangleEquipmentForCalculationDto;
use App\Dto\Calculation\Equipment\Calculate\RoundEquipmentForCalculationDto;
use App\Dto\Calculation\TotalLoad\EquipmentHeightTotalLoadDto;
use App\Dto\Calculation\TotalLoad\PillarSectionTotalLoadDto;
use App\Dto\Calculation\TotalLoad\PlatformSectionTotalLoadDto;
use App\Dto\Calculation\TotalLoad\TotalLoadResponseDto;
use App\Dto\DefaultConstant;
use App\Entity\CalculationData;
use App\Entity\CalculationEquipment;
use App\Entity\PillarPlatformSection;
use App\Enum\Equipment\EquipmentGroupEnum;
use App\Enum\Pillar\PlatformSectionTypeEnum;
use App\Exception\NotFoundException;
use App\Repository\CalculationEquipmentRepository;
use App\Repository\CalculationRepository;
use App\Service\Calculation\Pillar\PillarWindLoadCalculationService;

/**
 * Собирает суммарную нагрузку для таба 5:
 *   - Таблица 1: нагрузка по секциям ствола + коммуникаций
 *   - Таблица 2: нагрузка на площадку и надстройку
 *   - Таблица 3: нагрузка на оборудование, сгруппированная по высотной отметке
 */
final readonly class TotalLoadService
{
    /** Коэффициент перевода кг→Н (1 кгс = 9.81 Н) */
    private const KGF_TO_N = 9.81;

    /**
     * Cx для элементов площадки (пояса, раскосы) — трубчатые профили.
     * TODO: уточнить значение Cx у проектировщика (возможно зависит от типа сечения элемента).
     */
    private const PLATFORM_ELEMENT_CX = 1.4;

    public function __construct(
        private CalculationRepository $calculationRepository,
        private PillarWindLoadCalculationService $pillarWindLoadCalculationService,
        private CalculationEquipmentRepository $calculationEquipmentRepository,
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
        $this->fillPlatformSections($response, $calculation->getPillarPlatform()?->getSections() ?? [], $calculationData, $calculation->getPillarPlatform()?->getFacetsCount() ?? 1);
        $this->fillEquipmentHeights($response, $calculationId, $calculationData);

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

            $totalLoadN  = $totalLoadKgf * self::KGF_TO_N;
            $heightM     = $meta->heightSection > 0 ? $meta->heightSection / 1000 : 0;

            $response->addPillarSection(new PillarSectionTotalLoadDto(
                sectionNumber:      $meta->numberSection,
                topHeight:          $meta->topHeight,        // мм
                sectionHeight:      $meta->heightSection,    // мм
                totalLoad:          $totalLoadN,
                loadPerLinearMeter: $heightM > 0 ? $totalLoadN / $heightM : 0,
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
        array $platformSections,
        CalculationData $calculationData,
        int $facetsCount,
    ): void {
        if ($platformSections === []) {
            return;
        }

        $windRegion  = $calculationData->getWindRegion();
        $terrainType = $calculationData->getTerrainType();

        if ($windRegion === null || $terrainType === null) {
            // Недостаточно исходных данных для расчёта нагрузки на площадку
            return;
        }

        foreach ($platformSections as $section) {
            $isStrut   = $section->getTypeSection() === PlatformSectionTypeEnum::STRUT->value;
            $label     = $isStrut ? 'Подкосы' : (string) $section->getNumberSection();
            $topHeight = (float) ($section->getMountHeightTop() ?? 0);   // мм
            $height    = (float) ($section->getHeight() ?? 0);           // мм

            // Средняя высота секции для определения k(ze)
            $middleHeightM = (($section->getMountHeightTop() ?? 0) + ($section->getMountHeightBottom() ?? 0)) / 2 / 1000;

            $kze = $terrainType->roughnessCoefficient($middleHeightM);

            // Расчёт суммарной ветровой нагрузки на секцию по её элементам
            $totalLoadKgf = $this->calcPlatformSectionLoad(
                elements:     $section->getElements() ?? [],
                heightMm:     $height,
                kze:          $kze,
                windRegion:   $windRegion,
                facetsCount:  $facetsCount,
            );

            $totalLoadN = $totalLoadKgf * self::KGF_TO_N;
            $heightM    = $height > 0 ? $height / 1000 : 0;

            // Нагрузка на 1 п.м. 1 пояса = F / h / n_поясов
            $loadPerLinearMeterPerBelt = ($heightM > 0 && $facetsCount > 0)
                ? $totalLoadN / $heightM / $facetsCount
                : 0;

            $response->addPlatformSection(new PlatformSectionTotalLoadDto(
                label:                     $label,
                isStrut:                   $isStrut,
                topHeight:                 $topHeight,
                height:                    $height,
                totalLoad:                 $totalLoadN,
                loadPerLinearMeterPerBelt: $loadPerLinearMeterPerBelt,
            ));
        }
    }

    /**
     * Ветровое давление на секцию площадки по её элементам.
     *
     * Формула: P = W0 × k(ze) × γf × Cx × A × n_элементов × n_поясов
     * где A = ширина_элемента(м) × высота_секции(м)
     *
     * TODO: уточнить у проектировщика:
     *   - следует ли умножать на facetsCount здесь или элементы уже хранятся с учётом всех поясов;
     *   - корректный Cx для каждого типа сечения элемента (sectionType: "Труба круглая" / "Уголок" и т.д.).
     *
     * @param array<array{widthElement: int|float, lengthElement: int|float, countElement: int, type: string, sectionType: string}> $elements
     */
    private function calcPlatformSectionLoad(
        array $elements,
        float $heightMm,
        float $kze,
        mixed $windRegion,
        int $facetsCount,
    ): float {
        if ($elements === [] || $heightMm <= 0) {
            return 0;
        }

        $heightM  = $heightMm / 1000;
        $totalKgf = 0.0;

        foreach ($elements as $element) {
            $widthM = ((float) ($element['widthElement'] ?? 0)) / 1000;
            $count  = (int) ($element['countElement'] ?? 0);

            if ($widthM <= 0 || $count <= 0) {
                continue;
            }

            $area = $widthM * $heightM;

            $pressPerElement = $windRegion->pressureKgPerM()
                * $kze
                * DefaultConstant::SECURITY_COEFFICIENT
                * self::PLATFORM_ELEMENT_CX
                * $area;

            $totalKgf += $pressPerElement * $count;
        }

        // Умножаем на количество поясов, т.к. элементы хранятся для одного пояса
        return $totalKgf * $facetsCount;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Таблица 3: нагрузка на оборудование по высотным отметкам
    // ─────────────────────────────────────────────────────────────────────────

    private function fillEquipmentHeights(
        TotalLoadResponseDto $response,
        int $calculationId,
        CalculationData $calculationData,
    ): void {
        $equipments = $this->calculationEquipmentRepository->findByCalculationAndGroups(
            $calculationId,
            EquipmentGroupEnum::forCalculation(),
        );

        if ($equipments === []) {
            return;
        }

        // Группируем нагрузки по высотной отметке (mountingHeight хранится в метрах)
        $byHeight = [];

        foreach ($equipments as $equipment) {
            $mountHeightM   = (float) $equipment->getMountingHeight();
            $mountHeightMm  = $mountHeightM * 1000;
            $key            = number_format($mountHeightM, 2, '.', '');

            $calculator = $this->buildEquipmentCalculator($equipment, $calculationData);
            $loadN      = $calculator->totalLoad() * self::KGF_TO_N;

            if (! isset($byHeight[$key])) {
                $byHeight[$key] = [
                    'heightMark' => $mountHeightMm,
                    'totalLoad'  => 0.0,
                ];
            }

            $byHeight[$key]['totalLoad'] += $loadN;
        }

        // Сортируем по возрастанию высоты
        ksort($byHeight);

        // Высота интервала — расстояние от предыдущей отметки до текущей
        // TODO: уточнить у проектировщика, является ли «высота» интервалом между отметками
        //       или задаётся иным способом (например, высота группы оборудования).
        $prevHeightMm = 0.0;
        foreach ($byHeight as $row) {
            $intervalMm  = $row['heightMark'] - $prevHeightMm;
            $prevHeightMm = $row['heightMark'];

            $response->addEquipmentHeight(new EquipmentHeightTotalLoadDto(
                heightMark: $row['heightMark'],
                height:     $intervalMm,
                totalLoad:  $row['totalLoad'],
            ));
        }
    }

    private function buildEquipmentCalculator(
        CalculationEquipment $equipment,
        CalculationData $calculationData,
    ): EquipmentCalculator {
        $params = $equipment->getEquipmentParams();

        $equipmentGeometry = $equipment->getEquipmentType()->isRrl()
            ? new RoundEquipmentForCalculationDto(
                diameter: (float) $params['diameter'],
                weight:   (float) $params['weight'],
            )
            : new RectangleEquipmentForCalculationDto(
                height: (float) $params['height'],
                width:  (float) $params['width'],
                depth:  (float) $params['depth'],
                weight: (float) $params['weight'],
            );

        return new EquipmentCalculator(
            equipment:        $equipmentGeometry,
            windRegion:       $calculationData->getWindRegion(),
            terrainTypeEnum:  $calculationData->getTerrainType(),
            mountHeight:      (float) $equipment->getMountingHeight(),
            equipmentTypeEnum: $equipment->getEquipmentType(),
            quantity:         $equipment->getQuantity(),
        );
    }
}
