<?php

declare(strict_types=1);

namespace App\Service\DocumentGenerator\Report\Section\Tower;

use App\Entity\CalculationResultTable;
use App\Enum\Calculation\AnchorBoltSteelEnum;
use App\Enum\Calculation\FoundationLoadKindEnum;
use App\Enum\Calculation\ResultTableTypeEnum;
use App\Enum\Calculation\TowerWindDirectionEnum;
use App\Service\DocumentGenerator\DocStyleRegistry;
use App\Service\DocumentGenerator\Report\ReportContext;
use App\Service\DocumentGenerator\Report\Section\Result\ResultTableWriter;
use App\Service\DocumentGenerator\Report\SectionBuilderInterface;
use PhpOffice\PhpWord\Element\Section;

/**
 * Раздел «Результаты расчёта и выводы» для башни.
 *
 * Порядок: элементы каркаса (пояса, раскосы, распорки) → деформации → анкерные болты
 * → фланцевые болты → нагрузки на опорные узлы → сравнение с проектными нагрузками
 * → ссылка на расчёт фундамента → сводная таблица.
 */
final class TowerCalculationResultsSection implements SectionBuilderInterface
{
    private ResultTableWriter $writer;

    /**
     * @param int|null $foundationAppendixNum номер приложения «Расчёт фундамента», если оно есть
     */
    public function __construct(
        private readonly ?int $foundationAppendixNum = null,
    ) {
    }

    public function build(Section $section, ReportContext $context, int &$tableNum): void
    {
        $this->writer = new ResultTableWriter($section, $tableNum);

        $this->writer->stabilityTable($context, ResultTableTypeEnum::TOWER_BELT_STABILITY, 'напряжения в поясах');
        $this->writer->stabilityTable($context, ResultTableTypeEnum::TOWER_BRACE_STABILITY, 'напряжения в раскосах');
        $this->writer->stabilityTable($context, ResultTableTypeEnum::TOWER_SPACER_STABILITY, 'напряжения в распорках');
        $this->buildDeformation($section, $context);
        $this->buildAnchorBolts($section, $context);
        $this->buildFlangeBolts($section, $context);
        $this->buildFoundationLoads($section, $context);
        $this->buildLoadComparison($section, $context);
        $this->buildFoundationReference($section);
        $this->writer->equipmentSummaryTable(
            $context,
            $context->getTowerStructureMaxK(),
            $context->getTowerFoundationMaxK(),
        );

        $tableNum = $this->writer->tableNum();
    }

    // ─── Деформации ──────────────────────────────────────────────────────────

    private function buildDeformation(Section $section, ReportContext $context): void
    {
        $table = $this->enabledTable($context, ResultTableTypeEnum::TOWER_DEFORMATION);
        if ($table === null) {
            return;
        }

        $this->writer->caption('Деформации опоры от воздействия ветровых нагрузок:');

        $w = [3500, 3500, 3000];
        $tbl = $section->addTable(DocStyleRegistry::tableStyleReport());
        $rows = $table->getRows();
        $last = count($rows) - 1;

        $this->writer->addRow($tbl, $w, ['Перемещение (max), мм', 'Допустимое перемещение, мм', 'Кисп'], true, $last >= 0);
        foreach ($rows as $i => $row) {
            $this->writer->addRow($tbl, $w, [
                $this->writer->fmt($row['displacement'] ?? null, 0),
                $this->writer->fmt($row['displacementAllowable'] ?? null, 0),
                $this->writer->fmt($row['kUse'] ?? null, 2),
            ], false, $i < $last);
        }

        $maxRow = $this->writer->findMaxKRow($table, 'kUse');
        if ($maxRow !== null) {
            $kUse = (float)($maxRow['kUse'] ?? 0);
            $comply = $kUse <= 1.0;
            $style = $this->writer->verdictStyle($comply);
            $body = DocStyleRegistry::bodyText();

            $text = $section->addTextRun(DocStyleRegistry::paragraphIndent());
            $text->addText('Максимальное перемещение верхней отметки опоры от нормативных ветровых нагрузок составляет ', $body);
            $text->addText(sprintf('%.0f мм', (float)($maxRow['displacement'] ?? 0)), $style);
            $text->addText(' при допустимом ', $body);
            $text->addText(sprintf('%.0f мм', (float)($maxRow['displacementAllowable'] ?? 0)), DocStyleRegistry::titleTableTextUnderline());
            $text->addText(', ', $body);
            $text->addText(sprintf('Kисп=%.0f', $kUse * 100) . '%', $style);
            $text->addText(' (Н/100, где Н – высота башни), что ', $body);
            $text->addText($comply ? 'отвечает' : 'не отвечает', $style);
            $text->addText(' требованиям СП 16.13330.2017 «Стальные конструкции»;', $body);
        }

        $section->addTextBreak(1);
    }

    // ─── Анкерные болты ──────────────────────────────────────────────────────

    private function buildAnchorBolts(Section $section, ReportContext $context): void
    {
        $table = $this->enabledTable($context, ResultTableTypeEnum::TOWER_ANCHOR_BOLTS);
        if ($table === null) {
            return;
        }

        $this->writer->caption('Максимальные напряжения в анкерных болтах:');

        $w = [1100, 1000, 1300, 1200, 1400, 900, 1000, 1100, 1000];
        $tbl = $section->addTable(DocStyleRegistry::tableStyleReport());
        $rows = $table->getRows();
        $last = count($rows) - 1;

        $this->writer->addRow($tbl, $w, [
            'Диаметр болта, мм', 'Кол-во, шт', 'Площадь нетто Abn, см²', 'Nрасч, тс',
            'Класс прочности / марка стали', 'k₀', 'σ, Н/мм²', 'Rbt, Н/мм²', 'Кисп',
        ], true, $last >= 0);

        foreach ($rows as $i => $row) {
            $this->writer->addRow($tbl, $w, [
                $this->writer->fmt($row['diameter'] ?? null, 0),
                $this->writer->fmt($row['boltCount'] ?? null, 0),
                $this->writer->fmt($row['netArea'] ?? null, 2),
                $this->writer->fmt($row['maxLoad'] ?? null, 2),
                isset($row['steel']) ? (AnchorBoltSteelEnum::tryFrom($row['steel'])?->label() ?? '—') : '—',
                $this->writer->fmt($row['k0'] ?? null, 2),
                $this->writer->fmt($row['sigma'] ?? null, 0),
                $this->writer->fmt($row['rbt'] ?? null, 0),
                $this->writer->fmt($row['kUse'] ?? null, 2),
            ], false, $i < $last);
        }

        $this->writer->stressVerdict($this->writer->findMaxKRow($table, 'kUse'), 'напряжение в анкерных болтах', 'rbt');
        $section->addTextBreak(1);
    }

    // ─── Фланцевые болты ─────────────────────────────────────────────────────

    private function buildFlangeBolts(Section $section, ReportContext $context): void
    {
        $tension = $this->enabledTable($context, ResultTableTypeEnum::TOWER_FLANGE_BOLTS);
        $shear = $this->enabledTable($context, ResultTableTypeEnum::TOWER_FLANGE_BOLTS_SHEAR);

        if ($tension === null && $shear === null) {
            $section->addText(
                'В связи с тем, что информация по фланцевым болтам отсутствует, их расчёт не выполнялся.',
                DocStyleRegistry::bodyText(),
                DocStyleRegistry::paragraphIndent(),
            );
            $section->addTextBreak(1);
            return;
        }

        if ($tension !== null) {
            $this->buildFlangeBoltsTable(
                $section,
                $tension,
                'Максимальные напряжения в фланцевых болтах:',
                ['Площадь нетто Abn, см²', 'Rbt, Н/мм²'],
                ['netArea', 'rbp'],
                'напряжение в фланцевых болтах',
            );
        }

        if ($shear !== null) {
            $this->buildFlangeBoltsTable(
                $section,
                $shear,
                'Максимальные напряжения в фланцевых болтах на срез:',
                ['Площадь брутто Ab, см²', 'Rbs, Н/мм²'],
                ['grossArea', 'rbs'],
                'напряжение в фланцевых болтах на срез',
            );
        }
    }

    /**
     * Таблица фланцевых болтов: растяжение и срез отличаются только площадью
     * сечения болта и расчётным сопротивлением.
     *
     * @param array{string, string} $variableHeaders заголовки столбцов площади и сопротивления
     * @param array{string, string} $variableFields  поля строк площади и сопротивления
     */
    private function buildFlangeBoltsTable(
        Section $section,
        CalculationResultTable $table,
        string $title,
        array $variableHeaders,
        array $variableFields,
        string $description,
    ): void {
        $this->writer->caption($title);

        [$areaHeader, $limitHeader] = $variableHeaders;
        [$areaField, $limitField] = $variableFields;

        $w = [900, 1100, 1100, 1000, 1300, 1100, 1200, 1000, 1100, 900];
        $tbl = $section->addTable(DocStyleRegistry::tableStyleReport());
        $rows = $table->getRows();
        $last = count($rows) - 1;

        $this->writer->addRow($tbl, $w, [
            '№ узла', 'Отметка, м', 'Диаметр болта, мм', 'Кол-во, шт', $areaHeader,
            'Nрасч, тс', 'Класс прочности', 'σ, Н/мм²', $limitHeader, 'Кисп',
        ], true, $last >= 0);

        foreach ($rows as $i => $row) {
            $this->writer->addRow($tbl, $w, [
                $this->writer->fmt($row['jointNumber'] ?? null, 0),
                $this->writer->fmt($row['mark'] ?? null, 3),
                $this->writer->fmt($row['diameter'] ?? null, 0),
                $this->writer->fmt($row['boltCount'] ?? null, 0),
                $this->writer->fmt($row[$areaField] ?? null, 2),
                $this->writer->fmt($row['maxLoad'] ?? null, 2),
                (string)($row['strengthClass'] ?? '—'),
                $this->writer->fmt($row['sigma'] ?? null, 0),
                $this->writer->fmt($row[$limitField] ?? null, 0),
                $this->writer->fmt($row['kUse'] ?? null, 2),
            ], false, $i < $last);
        }

        $this->writer->stressVerdict($this->writer->findMaxKRow($table, 'kUse'), $description, $limitField);
        $section->addTextBreak(1);
    }

    // ─── Нагрузки на опорные узлы ────────────────────────────────────────────

    private function buildFoundationLoads(Section $section, ReportContext $context): void
    {
        $table = $this->enabledTable($context, ResultTableTypeEnum::TOWER_FOUNDATION_LOADS);
        if ($table === null) {
            return;
        }

        $this->writer->caption('Расчётные нагрузки, действующие на каждый опорный узел:');

        $w = [3000, 1600, 1800, 1800, 1800];
        $tbl = $section->addTable(DocStyleRegistry::tableStyleReport());
        $italic = DocStyleRegistry::italicCenter();
        $c = DocStyleRegistry::center();
        $centerKeep = array_merge(DocStyleRegistry::paragraphCenter(), ['keepNext' => true]);
        $dc = DocStyleRegistry::dataCell();

        $this->writer->addRow($tbl, $w, ['Направление ветра', '№ пояса', 'Rz, тс', 'Rx, тс', 'Ry, тс'], true);

        $groups = [];
        foreach ($table->getRows() as $row) {
            $groups[$row['direction'] ?? ''][] = $row;
        }

        foreach ($groups as $direction => $rows) {
            $label = TowerWindDirectionEnum::tryFrom((string)$direction)?->label() ?? '—';
            foreach ($rows as $i => $row) {
                $tbl->addRow(400, ['cantSplit' => true]);
                // Направление объединяется по вертикали на все пояса группы
                $directionCell = count($rows) === 1
                    ? $dc
                    : ['valign' => 'center', 'vMerge' => $i === 0 ? 'restart' : 'continue'];
                $cell = $tbl->addCell($w[0], $directionCell);
                if ($i === 0) {
                    $cell->addText($label, $italic, $centerKeep);
                }

                $values = [
                    $this->writer->fmt($row['beltNumber'] ?? null, 0),
                    $this->writer->fmt($row['rz'] ?? null, 1),
                    $this->writer->fmt($row['rx'] ?? null, 1),
                    $this->writer->fmt($row['ry'] ?? null, 1),
                ];
                foreach ($values as $j => $value) {
                    $tbl->addCell($w[$j + 1], $dc)->addText($value, $c, $centerKeep);
                }
            }
        }

        $section->addTextBreak(1);
    }

    // ─── Сравнение с проектными нагрузками ───────────────────────────────────

    private function buildLoadComparison(Section $section, ReportContext $context): void
    {
        $table = $this->enabledTable($context, ResultTableTypeEnum::TOWER_LOAD_COMPARISON);
        if ($table === null) {
            return;
        }

        $this->writer->caption('Сравнение расчётных нагрузок на фундаменты с проектными:');

        $w = [1900, 1350, 1350, 1350, 1350, 1350, 1350];
        $tbl = $section->addTable(DocStyleRegistry::tableStyleReport());
        $rows = $table->getRows();
        $last = count($rows) - 1;

        $this->writer->addRow($tbl, $w, [
            'Вид нагрузки',
            'Проектная вертикальная, тс', 'Проектная сдвигающая, тс',
            'Расчётная вертикальная, тс', 'Расчётная сдвигающая, тс',
            'Кисп (вертикальная)', 'Кисп (сдвигающая)',
        ], true, $last >= 0);

        foreach ($rows as $i => $row) {
            $this->writer->addRow($tbl, $w, [
                FoundationLoadKindEnum::tryFrom((string)($row['loadKind'] ?? ''))?->label() ?? '—',
                $this->writer->fmt($row['projectVertical'] ?? null, 1),
                $this->writer->fmt($row['projectShear'] ?? null, 1),
                $this->writer->fmt($row['calcVertical'] ?? null, 1),
                $this->writer->fmt($row['calcShear'] ?? null, 1),
                $this->writer->fmt($row['kUseVertical'] ?? null, 2),
                $this->writer->fmt($row['kUseShear'] ?? null, 2),
            ], false, $i < $last);
        }

        $kUse = $context->getTowerFoundationMaxK();
        if ($kUse !== null) {
            $comply = $kUse <= 1.0;
            $style = $this->writer->verdictStyle($comply);
            $body = DocStyleRegistry::bodyText();

            $text = $section->addTextRun(DocStyleRegistry::paragraphIndent());
            $text->addText('Расчётные нагрузки на фундаменты ', $body);
            $text->addText($comply ? 'не превышают' : 'превышают', $style);
            $text->addText(' проектные, ', $body);
            $text->addText(sprintf('Kисп=%.0f', $kUse * 100) . '%', $style);
            $text->addText('.', $body);
        }

        $section->addTextBreak(1);
    }

    private function buildFoundationReference(Section $section): void
    {
        if ($this->foundationAppendixNum === null) {
            return;
        }

        $section->addText(
            sprintf('Расчёт фундамента см. Приложение %d.', $this->foundationAppendixNum),
            DocStyleRegistry::bodyText(),
            DocStyleRegistry::paragraphIndent(),
        );
        $section->addTextBreak(1);
    }

    private function enabledTable(ReportContext $context, ResultTableTypeEnum $type): ?CalculationResultTable
    {
        $table = $context->getResultTable($type);

        return $table !== null && $table->isEnabled() ? $table : null;
    }
}
