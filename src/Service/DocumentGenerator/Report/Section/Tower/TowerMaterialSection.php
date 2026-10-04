<?php

declare(strict_types=1);

namespace App\Service\DocumentGenerator\Report\Section\Tower;

use App\Enum\Calculation\ResultTableTypeEnum;
use App\Service\DocumentGenerator\DocStyleRegistry;
use App\Service\DocumentGenerator\Report\ReportContext;
use App\Service\DocumentGenerator\Report\SectionBuilderInterface;
use PhpOffice\PhpWord\Element\Section;

/**
 * Раздел «Характеристики материала конструкций» для башни.
 * Расчётные сопротивления стали собираются из таблиц устойчивости элементов каркаса.
 */
final class TowerMaterialSection implements SectionBuilderInterface
{
    /** Ry по умолчанию, если в таблицах результатов значения не заданы */
    private const DEFAULT_RY = [240];

    private const STABILITY_TABLES = [
        ResultTableTypeEnum::TOWER_BELT_STABILITY,
        ResultTableTypeEnum::TOWER_BRACE_STABILITY,
        ResultTableTypeEnum::TOWER_SPACER_STABILITY,
    ];

    public function build(Section $section, ReportContext $context, int &$tableNum): void
    {
        $body = DocStyleRegistry::bodyText();
        $para = DocStyleRegistry::paragraphIndent();

        $section->addText('• модуль упругости стальных элементов 2,06 × 10⁵ Н/мм²;', $body, $para);
        $section->addText(
            sprintf('• расчётное сопротивление стали элементов башни принято %s Н/мм².', $this->formatRyList($context)),
            $body,
            $para,
        );

        $section->addTextBreak(1);
    }

    /** «240», «240 и 225», «240, 225 и 230» */
    private function formatRyList(ReportContext $context): string
    {
        $values = $this->collectRy($context) ?: self::DEFAULT_RY;
        $last = array_pop($values);

        return $values === [] ? (string)$last : implode(', ', $values) . ' и ' . $last;
    }

    /** @return int[] уникальные Ry в порядке появления */
    private function collectRy(ReportContext $context): array
    {
        $values = [];
        foreach (self::STABILITY_TABLES as $type) {
            $table = $context->getResultTable($type);
            if ($table === null || ! $table->isEnabled()) {
                continue;
            }
            foreach ($table->getRows() as $row) {
                if (! empty($row['ry'])) {
                    $values[] = (int)round((float)$row['ry']);
                }
            }
        }

        return array_values(array_unique($values));
    }
}
