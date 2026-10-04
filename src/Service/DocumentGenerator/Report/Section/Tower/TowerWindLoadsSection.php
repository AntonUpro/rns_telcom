<?php

declare(strict_types=1);

namespace App\Service\DocumentGenerator\Report\Section\Tower;

use App\Dto\Calculation\Platform\PlatformSectionDto;
use App\Dto\Calculation\Platform\TotalPlatformCalculationDto;
use App\Enum\Pillar\PlatformSectionTypeEnum;
use App\Exception\NotFoundException;
use App\Service\Calculation\Equipment\CalculationWindEquipmentService;
use App\Service\Calculation\Platform\PlatformCalculationService;
use App\Service\Calculation\Tower\TowerCommunicationsLoadCalculator;
use App\Service\DocumentGenerator\DocStyleRegistry;
use App\Service\DocumentGenerator\Report\ReportContext;
use App\Service\DocumentGenerator\Report\SectionBuilderInterface;
use App\Service\DocumentGenerator\Table\EquipmentWindPressureTableBuilder;
use App\Service\DocumentGenerator\Table\PlatformSectionsTableBuilder;
use App\Service\DocumentGenerator\Table\TowerCommunicationsTableBuilder;
use PhpOffice\PhpWord\Element\Section;

/**
 * Раздел «Горизонтальные нагрузки» для башни:
 *   8.1 — ветровое давление на каркас башни;
 *   8.2 — ветровое давление на оборудование и коммуникации.
 */
final readonly class TowerWindLoadsSection implements SectionBuilderInterface
{
    public function __construct(
        private PlatformCalculationService $platformService,
        private CalculationWindEquipmentService $equipmentWindService,
        private TowerCommunicationsLoadCalculator $communicationsCalculator,
        private PlatformSectionsTableBuilder $frameTableBuilder,
        private EquipmentWindPressureTableBuilder $equipmentTableBuilder,
        private TowerCommunicationsTableBuilder $communicationsTableBuilder,
    ) {
    }

    public function build(Section $section, ReportContext $context, int &$tableNum): void
    {
        $frame = $this->calculateFrame($context);

        $this->buildFrameSubsection($section, $frame, $tableNum);
        $section->addPageBreak();
        $this->buildEquipmentSubsection($section, $context, $frame, $tableNum);
    }

    private function buildFrameSubsection(Section $section, ?TotalPlatformCalculationDto $frame, int &$tableNum): void
    {
        $section->addTitle('8.1 ВЕТРОВОЕ ДАВЛЕНИЕ НА КАРКАС БАШНИ', 2);

        if ($frame === null || $frame->platformSections === []) {
            $this->addPlaceholder($section, '[Данные о секциях башни недоступны]');
            return;
        }

        $this->frameTableBuilder->build($section, $frame, $tableNum, 'Ветровое давление на каркас башни:');

        $section->addText(
            '*Ветровая нагрузка, указанная в таблице, при расчёте на ребро прикладывалась с коэф. k1=1,2 и на грань k1=1.',
            DocStyleRegistry::bodyText(),
            DocStyleRegistry::paragraphIndent(),
        );
        $section->addTextBreak(1);
    }

    private function buildEquipmentSubsection(
        Section $section,
        ReportContext $context,
        ?TotalPlatformCalculationDto $frame,
        int &$tableNum,
    ): void {
        $calculationId = $context->calculation->getId();

        $section->addTitle('8.2 ВЕТРОВОЕ ДАВЛЕНИЕ НА ОБОРУДОВАНИЕ', 2);
        $section->addText(
            'Состав оборудования принят в соответствии с предоставленной документацией'
            . ($context->getData()?->isSurveyPerformed() ? ' и результатами натурного обследования' : '')
            . ':',
            DocStyleRegistry::bodyText(),
            DocStyleRegistry::paragraphIndent(),
        );

        try {
            $this->equipmentTableBuilder->build($section, $this->equipmentWindService->calculate($calculationId), $tableNum);
            $this->equipmentTableBuilder->buildSummaryTable(
                $section,
                $this->equipmentWindService->calculateSummary($calculationId),
                $tableNum,
            );
        } catch (\Throwable) {
            $this->addPlaceholder($section, '[Данные об оборудовании недоступны]');
        }
        $section->addTextBreak(1);

        $communications = $frame !== null
            ? $this->communicationsCalculator->calculate($context->calculation, $frame)
            : [];
        if ($communications !== []) {
            $this->communicationsTableBuilder->build($section, $communications, $tableNum);
            $section->addTextBreak(1);
        }
    }

    /**
     * Ветровая нагрузка на секции каркаса (без подкосов).
     */
    private function calculateFrame(ReportContext $context): ?TotalPlatformCalculationDto
    {
        try {
            $platform = $this->platformService->calculatePlatform($context->calculation->getId());
        } catch (NotFoundException) {
            return null;
        }

        return new TotalPlatformCalculationDto(array_values(array_filter(
            $platform->platformSections,
            static fn(PlatformSectionDto $section): bool => $section->type === PlatformSectionTypeEnum::SECTION,
        )));
    }

    private function addPlaceholder(Section $section, string $text): void
    {
        $section->addText($text, DocStyleRegistry::bodyText(), DocStyleRegistry::paragraphLeft());
    }
}
