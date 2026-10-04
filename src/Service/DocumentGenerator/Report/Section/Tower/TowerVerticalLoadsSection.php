<?php

declare(strict_types=1);

namespace App\Service\DocumentGenerator\Report\Section\Tower;

use App\Exception\NotFoundException;
use App\Service\Calculation\Platform\PlatformCalculationService;
use App\Service\Calculation\Tower\TowerIceLoadCalculator;
use App\Service\DocumentGenerator\DocStyleRegistry;
use App\Service\DocumentGenerator\Report\ReportContext;
use App\Service\DocumentGenerator\Report\Section\VerticalLoadsSection;
use App\Service\DocumentGenerator\Report\SectionBuilderInterface;
use App\Service\DocumentGenerator\Table\TowerIceLoadTableBuilder;
use PhpOffice\PhpWord\Element\Section;

/**
 * Раздел «Вертикальные нагрузки» для башни:
 *   9.1 — собственный вес (общий текст с отчётом столба);
 *   9.2 — гололёд по секциям каркаса.
 */
final readonly class TowerVerticalLoadsSection implements SectionBuilderInterface
{
    public function __construct(
        private PlatformCalculationService $platformService,
        private TowerIceLoadCalculator $iceLoadCalculator,
        private TowerIceLoadTableBuilder $iceLoadTableBuilder,
    ) {
    }

    public function build(Section $section, ReportContext $context, int &$tableNum): void
    {
        $section->addTitle('9.1 СОБСТВЕННЫЙ ВЕС', 2);
        (new VerticalLoadsSection())->build($section, $context, $tableNum);

        try {
            $frame = $this->platformService->calculatePlatform($context->calculation->getId());
            $rows = $this->iceLoadCalculator->calculate($context->calculation, $frame);
        } catch (NotFoundException) {
            $rows = [];
        }

        if ($rows === []) {
//            $section->addText(
//                '[Недостаточно данных для расчёта гололёдной нагрузки: не заданы секции башни, гололёдный район или тип местности]',
//                DocStyleRegistry::bodyText(),
//                DocStyleRegistry::paragraphLeft(),
//            );
            return;
        }
        $section->addTitle('9.2 ГОЛОЛЁД', 2);
        $this->iceLoadTableBuilder->build($section, $rows, $tableNum);
        $section->addTextBreak(1);
    }
}
