<?php

declare(strict_types=1);

namespace App\Service\DocumentGenerator\Report\Section;

use App\Exception\NotFoundException;
use App\Service\Calculation\Equipment\CalculationWindEquipmentService;
use App\Service\Calculation\Pillar\Pillar\PillarWindLoadCalculationService;
use App\Service\Calculation\PillarPlatform\PillarPlatformCalculationService;
use App\Service\DocumentGenerator\DocStyleRegistry;
use App\Service\DocumentGenerator\Report\ReportContext;
use App\Service\DocumentGenerator\Report\SectionBuilderInterface;
use App\Service\DocumentGenerator\Table\EquipmentWindPressureTableBuilder;
use App\Service\DocumentGenerator\Table\PillarSectionsTableBuilder;
use App\Service\DocumentGenerator\Table\PlatformSectionsTableBuilder;
use PhpOffice\PhpWord\Element\Section;

/**
 * Раздел «Горизонтальные нагрузки» — ветровые нагрузки.
 * Включает подразделы:
 *   8.1  Ветровое давление на ствол опоры (+ площадку, если есть)
 *   8.2  Ветровое давление на оборудование
 */
final class WindLoadsSection implements SectionBuilderInterface
{
    public function __construct(
        private readonly PillarWindLoadCalculationService $pillarWindService,
        private readonly CalculationWindEquipmentService $equipmentWindService,
        private readonly PillarPlatformCalculationService $platformService,
        private readonly PillarSectionsTableBuilder $pillarBuilder,
        private readonly EquipmentWindPressureTableBuilder $equipmentBuilder,
        private readonly PlatformSectionsTableBuilder $platformBuilder,
    ) {
    }

    public function build(Section $section, ReportContext $context, int &$tableNum): void
    {
        $calcId = $context->calculation->getId();

        $this->buildPillarSubsection($section, $calcId, $tableNum);
        $this->buildEquipmentSubsection($section, $calcId, $tableNum);
    }

    private function buildPillarSubsection(Section $section, int $calculationId, int &$tableNum): void
    {
        $section->addTitle('8.1 ВЕТРОВОЕ ДАВЛЕНИЕ НА СТВОЛ ОПОРЫ', 2);

        $section->addText(
            'Состав нагрузки принят в соответствии с предоставленной документацией '
            . 'и результатами натурного обследования.',
            DocStyleRegistry::bodyText(),
            DocStyleRegistry::paragraphIndent(),
        );

        try {
            $pillarSections = $this->pillarWindService->calculate($calculationId);
            $this->pillarBuilder->build($section, $pillarSections, $tableNum);
        } catch (\Throwable) {
            $section->addText('[Данные о стволе опоры недоступны]', DocStyleRegistry::bodyText(), DocStyleRegistry::paragraphLeft());
        }

        $section->addTextBreak(1);

        try {
            $platformData = $this->platformService->calculatePillarPlatform($calculationId);
            if (! empty($platformData->platformSections)) {
                $this->platformBuilder->build($section, $platformData, $tableNum);
                $section->addTextBreak(1);
            }
        } catch (NotFoundException) {
            // площадка отсутствует — пропускаем
        }
    }

    private function buildEquipmentSubsection(Section $section, int $calcId, int &$tableNum): void
    {
        $section->addTitle('8.2 ВЕТРОВОЕ ДАВЛЕНИЕ НА ОБОРУДОВАНИЕ', 2);

        try {
            $equipmentData = $this->equipmentWindService->calculate($calcId);
            $this->equipmentBuilder->build($section, $equipmentData, $tableNum);

            $summaryData = $this->equipmentWindService->calculateSummary($calcId);
            $this->equipmentBuilder->buildSummaryTable($section, $summaryData, $tableNum);
        } catch (\Throwable) {
            $section->addText('[Данные об оборудовании недоступны]', DocStyleRegistry::bodyText(), DocStyleRegistry::paragraphLeft());
        }

        $section->addTextBreak(1);
    }
}
