<?php

declare(strict_types=1);

namespace App\Service\DocumentGenerator\Report\Section;

use App\Enum\Calculation\ResultTableTypeEnum;
use App\Enum\Gauge\GaugeProfileTypeEnum;
use App\Service\DocumentGenerator\DocStyleRegistry;
use App\Service\DocumentGenerator\Report\ReportContext;
use App\Service\DocumentGenerator\Report\SectionBuilderInterface;
use App\Service\DocumentGenerator\Report\Section\Result\ResultTableWriter;
use PhpOffice\PhpWord\Element\Section;
use PhpOffice\PhpWord\Element\Table;

/**
 * Раздел «Результаты расчёта и выводы».
 * Строит таблицы по каждому типу из calculation_result_table.
 */
final class CalculationResultsSection implements SectionBuilderInterface
{
    private ResultTableWriter $writer;

    public function build(Section $section, ReportContext $context, int &$tableNum): void
    {
        $this->writer = new ResultTableWriter($section, $tableNum);

        $this->buildPillarForces($section, $context);
        $this->buildStressTable($section, $context, ResultTableTypeEnum::BRACE_STRESS, 'напряжения в элементах подкосов', 'СП 16.13330.2017 «Стальные конструкции»');
        $this->buildStressTable($section, $context, ResultTableTypeEnum::PLATFORM_FORCES, 'напряжения в элементах площадки', 'СП 16.13330.2017 «Стальные конструкции»');
        $this->buildStressTable($section, $context, ResultTableTypeEnum::SUPERSTRUCTURE_STRESS, 'напряжения в элементах поясов надстройки', 'СП 16.13330.2017 «Стальные конструкции»');
        $this->writer->stabilityTable($context, ResultTableTypeEnum::SUPERSTRUCTURE_STABILITY_BELT, 'напряжения в поясах надстройки (устойчивость)');
        $this->writer->stabilityTable($context, ResultTableTypeEnum::SUPERSTRUCTURE_STABILITY_BRACE, 'напряжения в элементах раскосов надстройки (устойчивость)');
        $this->buildDeformation($section, $context);
        $this->buildBaseForces($section, $context);
        $this->buildFoundation($section, $context);
        $this->buildSummaryTable($section, $context);

        $tableNum = $this->writer->tableNum();
    }

    // ─── Усилия в стволе опоры ────────────────────────────────────────────────

    private function buildPillarForces(Section $section, ReportContext $context): void
    {
        $table = $context->getResultTable(ResultTableTypeEnum::PILLAR_FORCES);
        if ($table === null) {
            return;
        }

        $num = $this->writer->nextTableNum();
        $section->addText(
            'Максимальные усилия в стволе опоры от расчётных нагрузок:',
            DocStyleRegistry::titleTableTextUnderline(),
            DocStyleRegistry::paragraphIndentWithKeepNext(),
        );
        $section->addText('Таблица ' . $num, DocStyleRegistry::normalText(), DocStyleRegistry::paragraphRight());

        $w = [400, 1600, 2100, 2000, 2000, 1900];
        $tbl = $section->addTable(DocStyleRegistry::tableStyleReport());

        $rows = $table->getRows();

        $specificData = $context->calculation?->getCalculationData()?->getConcretePillarSpecificData();
        $strengtheningHeight = ($specificData?->strengtheningExist ?? false)
            ? $specificData->strengthening?->strengtheningHeight
            : null;

        if ($strengtheningHeight !== null && $strengtheningHeight > 0) {
            $pillarHeight = (float)($specificData?->pillarHeight ?? 0);
            $this->buildPillarForcesWithStrengthening($section, $tbl, $w, $rows, $strengtheningHeight, $pillarHeight);
            $section->addTextBreak(1);

            return;
        }

        $last = count($rows) - 1;

        $this->writer->addRow($tbl, $w, ['№', 'Отметка, м', 'Тип опоры', 'Mрасч, тс·м', 'Мдоп, тс·м', 'Кисп'], true, $last >= 0);

        foreach ($rows as $i => $row) {
            if ($i !== 0) {
                continue;
            }
            $this->writer->addRow($tbl, $w, [
                (string)($i + 1),
                $this->writer->fmt($row['mark'] ?? null),
                (string)($row['pillarType'] ?? '—'),
                $this->writer->fmt($row['mCalc'] ?? null, 3),
                $this->writer->fmt($row['mAllowable'] ?? null, 3),
                $this->writer->fmt($row['kMax'] ?? null, 3),
            ], false, $i < $last);
        }

        $maxRow = $this->writer->findMaxKRow($table, 'kMax');
        if ($maxRow !== null) {
            $comply = ((float)($maxRow['kMax'] ?? 0)) <= 1.0;

            $style = $comply ? DocStyleRegistry::titleTableTextUnderline() : DocStyleRegistry::titleTableTextUnderlineBold();

            $textRun = $section->addTextRun(DocStyleRegistry::paragraphIndent());
            $textRun->addText('Максимальное усилие в стволе опоры ', DocStyleRegistry::bodyText());
            $textRun->addText(sprintf('%.2f', (float)($maxRow['mCalc'] ?? 0)), $style);
            $textRun->addText(' тс·м при допустимом ', DocStyleRegistry::bodyText());
            $textRun->addText(sprintf('%.2f', (float)($maxRow['mAllowable'] ?? 0)), $style);
            $textRun->addText(' тс·м, ', DocStyleRegistry::bodyText());
            $textRun->addText(sprintf('Кисп=%d%%', (int)round((float)($maxRow['kMax'] ?? 0) * 100)), $style);
            $textRun->addText(', что ', DocStyleRegistry::bodyText());
            $textRun->addText($comply ? 'удовлетворяет' : 'не удовлетворяет', $style);
            $textRun->addText(' требованиям СП 63.13330.2018;', DocStyleRegistry::bodyText());
        }

        $section->addTextBreak(1);
    }

    /**
     * При усилении ж/б столба таблица «Максимальные усилия в стволе опоры»
     * содержит 2 строки: Кисп в пределах усиления (от 0 до высоты усиления)
     * и Кисп выше усиления (от высоты усиления до верха опоры), каждая —
     * со своим абзацем-выводом под таблицей.
     */
    private function buildPillarForcesWithStrengthening(
        Section $section,
        Table $tbl,
        array $w,
        array $rows,
        float $strengtheningHeight,
        float $pillarHeight,
    ): void {
        $reinforcedRows = array_values(array_filter(
            $rows,
            static fn(array $row): bool => ((float)($row['mark'] ?? 0)) <= $strengtheningHeight,
        ));
        $aboveRows = array_values(array_filter(
            $rows,
            static fn(array $row): bool => ((float)($row['mark'] ?? 0)) > $strengtheningHeight,
        ));

        $groups = [
            [$reinforcedRows, 0.0, $strengtheningHeight],
            [$aboveRows, $strengtheningHeight, $pillarHeight],
        ];

        $last = count($groups) - 1;
        $this->writer->addRow($tbl, $w, ['№', 'Отметка, м', 'Тип опоры', 'Mрасч, тс·м', 'Мдоп, тс·м', 'Кисп'], true, $last >= 0);

        foreach ($groups as $i => [$groupRows, $fromMark, $toMark]) {
            $maxRow = $this->writer->findMaxKRowFromRows($groupRows, 'kMax');
            if ($maxRow === null) {
                continue;
            }

            $this->writer->addRow($tbl, $w, [
                (string)($i + 1),
                $this->writer->fmt($maxRow['mark'] ?? null),
                (string)($maxRow['pillarType'] ?? '—'),
                $this->writer->fmt($maxRow['mCalc'] ?? null, 3),
                $this->writer->fmt($maxRow['mAllowable'] ?? null, 3),
                $this->writer->fmt($maxRow['kMax'] ?? null, 3),
            ], false, $i < $last);

            $comply = ((float)($maxRow['kMax'] ?? 0)) <= 1.0;
            $style = $comply ? DocStyleRegistry::titleTableTextUnderline() : DocStyleRegistry::titleTableTextUnderlineBold();

            $textRun = $section->addTextRun(DocStyleRegistry::paragraphIndent());
            $textRun->addText(
                sprintf(
                    'Максимальное усилие в стволе опоры от %s до %s м составляет ',
                    self::formatMark($fromMark),
                    self::formatMark($toMark),
                ),
                DocStyleRegistry::bodyText(),
            );
            $textRun->addText(sprintf('%.2f', (float)($maxRow['mCalc'] ?? 0)), $style);
            $textRun->addText(' тс·м при допустимом ', DocStyleRegistry::bodyText());
            $textRun->addText(sprintf('%.2f', (float)($maxRow['mAllowable'] ?? 0)), $style);
            $textRun->addText(' тс·м, ', DocStyleRegistry::bodyText());
            $textRun->addText(sprintf('Кисп=%d%%', (int)round((float)($maxRow['kMax'] ?? 0) * 100)), $style);
            $textRun->addText(', что ', DocStyleRegistry::bodyText());
            $textRun->addText($comply ? 'удовлетворяет' : 'не удовлетворяет', $style);
            $textRun->addText(' требованиям СП 63.13330.2018;', DocStyleRegistry::bodyText());
        }
    }

    private static function formatMark(float $mark): string
    {
        $formatted = number_format(abs($mark), 3, ',', '');

        return match (true) {
            $mark > 0 => '+' . $formatted,
            $mark < 0 => '-' . $formatted,
            default => $formatted,
        };
    }

    // ─── Раскрытие трещин ────────────────────────────────────────────────────

    private function buildCrackOpening(Section $section, ReportContext $context): void
    {
        $table = $context->getResultTable(ResultTableTypeEnum::CRACK_OPENING);
        if ($table === null) {
            return;
        }

        $num = $this->writer->nextTableNum();
        $section->addText(
            'Максимальное раскрытие трещин в стволе опоры от нормативных нагрузок:',
            DocStyleRegistry::titleTableTextUnderline(),
            DocStyleRegistry::paragraphIndentWithKeepNext(),
        );
        $section->addText('Таблица ' . $num, DocStyleRegistry::normalText(), DocStyleRegistry::paragraphRight());

        $w = [400, 1300, 1600, 2100, 2300, 1700];
        $tbl = $section->addTable(DocStyleRegistry::tableStyleReport());

        $rows = $table->getRows();
        $last = count($rows) - 1;

        $this->writer->addRow($tbl, $w, ['№', 'Отметка, м', 'Тип опоры', 'Расч. ширина трещин, мм', 'Пред. доп. ширина, мм', 'k(max)'], true, $last >= 0);

        foreach ($rows as $i => $row) {
            $this->writer->addRow($tbl, $w, [
                (string)($i + 1),
                $this->writer->fmt($row['mark'] ?? null),
                (string)($row['pillarType'] ?? '—'),
                $this->writer->fmt($row['crackWidthCalc'] ?? null, 4),
                $this->writer->fmt($row['crackWidthAllowable'] ?? null, 4),
                $this->writer->fmt($row['kMax'] ?? null, 3),
            ], false, $i < $last);
        }

        $maxRow = $this->writer->findMaxKRow($table, 'kMax');
        if ($maxRow !== null) {
            $comply = ((float)($maxRow['kMax'] ?? 0)) <= 1.0;

            $style = $comply ? DocStyleRegistry::titleTableTextUnderline() : DocStyleRegistry::titleTableTextUnderlineBold();

            $textRun = $section->addTextRun(DocStyleRegistry::paragraphIndent());
            $textRun->addText('Максимальное раскрытие трещин в стволе опоры ', DocStyleRegistry::bodyText());
            $textRun->addText(sprintf('%.2f', (float)($maxRow['crackWidthCalc'] ?? 0)), $style);
            $textRun->addText(' мм при допустимом ', DocStyleRegistry::bodyText());
            $textRun->addText(sprintf('%.2f', ($maxRow['crackWidthAllowable'] ?? 0)), $style);
            $textRun->addText(' мм, ', DocStyleRegistry::bodyText());
            $textRun->addText(sprintf('Кисп=%d%%', (int)round((float)($maxRow['kMax'] ?? 0) * 100)), $style);
            $textRun->addText(', что ', DocStyleRegistry::bodyText());
            $textRun->addText($comply ? 'удовлетворяет' : 'не удовлетворяет', $style);
            $textRun->addText(' требованиям СП 63.13330.2018;', DocStyleRegistry::bodyText());
        }

        $section->addTextBreak(1);
    }

    // ─── Таблица напряжений (подкосы / площадка / надстройка) ────────────────

    private function buildStressTable(
        Section $section,
        ReportContext $context,
        ResultTableTypeEnum $type,
        string $description,
        string $normRef,
    ): void {
        $table = $context->getResultTable($type);
        if ($table === null || ! $table->isEnabled()) {
            return;
        }

        $num = $this->writer->nextTableNum();
        $section->addText(
            'Максимальные ' . $description . ':',
            DocStyleRegistry::titleTableTextUnderline(),
            DocStyleRegistry::paragraphIndentWithKeepNext(),
        );
        $section->addText('Таблица ' . $num, DocStyleRegistry::normalText(), DocStyleRegistry::paragraphRight());

        $w = [1200, 1200, 1200, 1000, 900, 900, 900, 900, 900, 900];
        $tbl = $section->addTable(DocStyleRegistry::tableStyleReport());

        $rows = $table->getRows();
        $last = count($rows) - 1;

        $this->writer->addRow($tbl, $w, [
            'Отм., м', 'Элемент', 'Сечение',
            'A, см²', 'Wy, см³', 'N, тс', 'M, тс·м',
            'Ry, Н/мм²', 'σ, Н/мм²', 'Кисп',
        ], true, $last >= 0);

        foreach ($rows as $i => $row) {
            $this->writer->addRow($tbl, $w, [
                $this->writer->fmt($row['mark'] ?? null, 3),
                (string)($row['element'] ?? '—'),
//                $row['profileType'] ? GaugeProfileTypeEnum::from($row['profileType'])->label() : '—',
                GaugeProfileTypeEnum::from($row['profileType'])->icon() . ($row['sectionDesignation'] ?? '—'),
                $this->writer->fmt($row['area'] ?? null, 2),
                $this->writer->fmt($row['momentResistance'] ?? null, 2),
                $this->writer->fmt($row['nCalc'] ?? null, 2),
                $this->writer->fmt($row['mCalc'] ?? null, 2),
                $this->writer->fmt($row['sigma'] ?? null, 0),
                $this->writer->fmt($row['ry'] ?? null, 0),
                $this->writer->fmt($row['kUse'] ?? null, 2),
            ], false, $i < $last);
        }

        $this->writer->stressVerdict($this->writer->findMaxKRow($table, 'kUse'), $description);

        $section->addTextBreak(1);
    }

    // ─── Деформации ──────────────────────────────────────────────────────────

    private function buildDeformation(Section $section, ReportContext $context): void
    {
        $table = $context->getResultTable(ResultTableTypeEnum::DEFORMATION);
        if ($table === null || ! $table->isEnabled()) {
            return;
        }

        $num = $this->writer->nextTableNum();
        $section->addText(
            'Деформации опоры от воздействия ветровых нагрузок:',
            DocStyleRegistry::titleTableTextUnderline(),
            DocStyleRegistry::paragraphIndentWithKeepNext(),
        );
        $section->addText('Таблица ' . $num, DocStyleRegistry::normalText(), DocStyleRegistry::paragraphRight());

        $w = [400, 1600, 2000, 2000, 2000, 2000];
        $tbl = $section->addTable(DocStyleRegistry::tableStyleReport());

        $rows = $table->getRows();
        $last = count($rows) - 1;

        $this->writer->addRow($tbl, $w, [
            '№', 'Отметка, м', 'Перемещение, мм',
            'Верт. угол (max), град.', 'Допустимый вертикальный угол, град.', 'Кисп',
        ], true, $last >= 0);

        foreach ($rows as $i => $row) {
            $this->writer->addRow($tbl, $w, [
                (string)($i + 1),
                $this->writer->fmt($row['mark'] ?? null),
                $this->writer->fmt($row['displacement'] ?? null, 1),
                $this->writer->fmt($row['angleMax'] ?? null, 2),
                $this->writer->fmt($row['angleAllowable'] ?? null, 2),
                $this->writer->fmt($row['kUse'] ?? null, 2),
            ], false, $i < $last);
        }

        $maxRow = $this->writer->findMaxKRow($table, 'kUse');
        if ($maxRow !== null) {
            $comply = ((float)($maxRow['kUse'] ?? 0)) <= 1.0;

            $style = $comply ? DocStyleRegistry::titleTableTextUnderline() : DocStyleRegistry::titleTableTextUnderlineBold();

            $text = $section->addTextRun(DocStyleRegistry::paragraphIndent());
            $text->addText('Максимальное перемещение верхней отметки опоры от нормативных ветровых нагрузок составляет ', DocStyleRegistry::bodyText());
            $text->addText(sprintf('%.0f мм', (float)($maxRow['displacement'] ?? 0)), DocStyleRegistry::titleTableTextUnderline());
            $text->addText(', максимальный вертикальный угол отклонения ', DocStyleRegistry::bodyText());
            $text->addText(sprintf('%.2fº', (float)($maxRow['angleMax'] ?? 0)), $style);
            $text->addText('. ', DocStyleRegistry::bodyText());

            $text->addText('Деформации ствола опоры ', DocStyleRegistry::bodyText());
            $text->addText($comply ? 'соответствуют' : 'не соответствуют', $style);
            $text->addText(' требованиям нормативной документации.', DocStyleRegistry::bodyText());
        }

        $section->addTextBreak(1);
    }

    // ─── Опорные реакции ──────────────────────────────────────────────────────

    private function buildBaseForces(Section $section, ReportContext $context): void
    {
        $table = $context->getResultTable(ResultTableTypeEnum::BASE_PILLAR_FORCES);
        if ($table === null || ! $table->isEnabled()) {
            return;
        }

        $num = $this->writer->nextTableNum();
        $section->addText('Расчетные нагрузки, возникающие в уровне заделки стойки:', DocStyleRegistry::titleTableTextUnderline(), DocStyleRegistry::paragraphIndentWithKeepNext());
        $section->addText('Таблица ' . $num, DocStyleRegistry::normalText(), DocStyleRegistry::paragraphRight());

        $w = [400, 3600, 2000, 2000, 2000];
        $tbl = $section->addTable(DocStyleRegistry::tableStyleReport());

        $rows = $table->getRows();
        $last = count($rows) - 1;

        $this->writer->addRow($tbl, $w, ['№', 'Тип нагрузки', 'N, тс', 'Q, тс', 'М, тс·м'], true, $last >= 0);

        foreach ($rows as $i => $row) {
            $this->writer->addRow($tbl, $w, [
                (string)($i + 1),
                (string)($row['loadType'] ?? '—'),
                $this->writer->fmt($row['n'] ?? null, 1),
                $this->writer->fmt($row['q'] ?? null, 1),
                $this->writer->fmt($row['m'] ?? null, 1),
            ], false, $i < $last);
        }

        $section->addTextBreak(1);
    }

    // ─── Расчёт основания ────────────────────────────────────────────────────

    private function buildFoundation(Section $section, ReportContext $context): void
    {
        $table = $context->getResultTable(ResultTableTypeEnum::FOUNDATION);
        if ($table === null || ! $table->isEnabled()) {
            return;
        }

        $num = $this->writer->nextTableNum();
        $section->addText(
            'Результаты расчёта основания опоры:',
            DocStyleRegistry::titleTableTextUnderline(),
            DocStyleRegistry::paragraphIndentWithKeepNext(),
        );
        $section->addText('Таблица ' . $num, DocStyleRegistry::normalText(), DocStyleRegistry::paragraphRight());

        // Двухуровневый заголовок
        $totalW = 10000;
        $wStab = [1500, 1500];
        $wDef = [1500, 1500];
        $wKuse = [2000, 2000];
        $tbl = $section->addTable(DocStyleRegistry::tableStyleReport());
        $h = DocStyleRegistry::headerCell();
        $italic = DocStyleRegistry::italicCenter();
        $center = DocStyleRegistry::paragraphCenter();
        $centerKeep = array_merge($center, ['keepNext' => true]);

        $rows = $table->getRows();
        $last = count($rows) - 1;

        // Row 1: group headers
        $tbl->addRow(500, ['cantSplit' => true]);
        $tbl->addCell($wStab[0] + $wStab[1], array_merge($h, ['gridSpan' => 2]))->addText('Устойчивость', $italic, $centerKeep);
        $tbl->addCell($wDef[0] + $wDef[1], array_merge($h, ['gridSpan' => 2]))->addText('Деформации', $italic, $centerKeep);
        $tbl->addCell($wKuse[0] + $wKuse[1], array_merge($h, ['gridSpan' => 2]))->addText('Коэффициент использования', $italic, $centerKeep);

        // Row 2: sub-headers
        $tbl->addRow(500, ['cantSplit' => true]);
        $subHeaderPara = $last >= 0 ? $centerKeep : $center;
        $tbl->addCell($wStab[0], $h)->addText('Q, тс', $italic, $subHeaderPara);
        $tbl->addCell($wStab[1], $h)->addText('Qu, тс', $italic, $subHeaderPara);
        $tbl->addCell($wDef[0], $h)->addText('β, рад.', $italic, $subHeaderPara);
        $tbl->addCell($wDef[1], $h)->addText('βu, рад.', $italic, $subHeaderPara);
        $tbl->addCell($wKuse[0], $h)->addText('Расч. на устойч.', $italic, $subHeaderPara);
        $tbl->addCell($wKuse[1], $h)->addText('Расч. по деф.', $italic, $subHeaderPara);

        // Data rows
        $dc = DocStyleRegistry::dataCell();
        $c = DocStyleRegistry::center();

        foreach ($rows as $i => $row) {
            $rowPara = $i < $last ? $centerKeep : $center;
            $tbl->addRow(400, ['cantSplit' => true]);
            $tbl->addCell($wStab[0], $dc)->addText($this->writer->fmt($row['q'] ?? null, 3), $c, $rowPara);
            $tbl->addCell($wStab[1], $dc)->addText($this->writer->fmt($row['qU'] ?? null, 3), $c, $rowPara);
            $tbl->addCell($wDef[0], $dc)->addText($this->writer->fmt($row['beta'] ?? null, 4), $c, $rowPara);
            $tbl->addCell($wDef[1], $dc)->addText($this->writer->fmt($row['betaU'] ?? null, 4), $c, $rowPara);
            $tbl->addCell($wKuse[0], $dc)->addText($this->writer->fmt($row['kUseStability'] ?? null, 3), $c, $rowPara);
            $tbl->addCell($wKuse[1], $dc)->addText($this->writer->fmt($row['kUseDeformation'] ?? null, 3), $c, $rowPara);
        }

        // Summary per row type
        foreach ($table->getRows() as $row) {
            $ks = $row['kUseStability'] ?? null;
            $kd = $row['kUseDeformation'] ?? null;
            if ($ks !== null) {
                $textKs = $section->addTextRun(DocStyleRegistry::paragraphIndent());

                $comply = (float)$ks <= 1.0;

                $style = $comply ? DocStyleRegistry::titleTableTextUnderline() : DocStyleRegistry::titleTableTextUnderlineBold();

                $textKs->addText('Поперечная сила от действия расчетных нагрузок ', DocStyleRegistry::bodyText());
                $textKs->addText(sprintf('Qmax=%.2f т', $row['q'],), DocStyleRegistry::titleTableTextUnderline());
                $textKs->addText(', полученная в результате расчета опоры, ', DocStyleRegistry::bodyText());
                $textKs->addText($comply ? 'не превышает' : 'превышает', $style);
                $textKs->addText(' предельную горизонтальную силу ', DocStyleRegistry::bodyText());
                $textKs->addText(sprintf('Q=%.2f т', $row['qU'],), DocStyleRegistry::titleTableTextUnderline());
                $textKs->addText(', ', DocStyleRegistry::bodyText());
                $textKs->addText(sprintf('Кисп=%.0f', $ks * 100) . '%.', $style);
            }
            if ($kd !== null) {
                $textKd = $section->addTextRun(DocStyleRegistry::paragraphIndent());

                $comply = (float)$kd <= 1.0;
                $style = $comply ? DocStyleRegistry::titleTableTextUnderline() : DocStyleRegistry::titleTableTextUnderlineBold();

                $comply = (float)$kd <= 1.0;
                $textKd->addText('Деформации опоры от действия нормативных нагрузок: ', DocStyleRegistry::bodyText());
                $textKd->addText(sprintf('β= %.4f рад', $row['beta']), DocStyleRegistry::titleTableTextUnderline());
                $textKd->addText(', ', DocStyleRegistry::bodyText());
                $textKd->addText($comply ? 'не превышают' : 'превышают', $style);
                $textKd->addText(' предельно допустимое значение ', DocStyleRegistry::bodyText());
                $textKd->addText(sprintf('β= %.2f рад', $row['betaU']), DocStyleRegistry::titleTableTextUnderline());
                $textKd->addText(', ', DocStyleRegistry::bodyText());
                $textKd->addText(sprintf('Кисп=%.0f', $kd * 100) . '%.', $style);
            }
        }

        $section->addTextBreak(1);
    }

    // ─── Сводная таблица ──────────────────────────────────────────────────────

    private function buildSummaryTable(Section $section, ReportContext $context): void
    {
        $pillarForces = $context->getResultTable(ResultTableTypeEnum::PILLAR_FORCES);
        $pillarKuse = $pillarForces !== null
            ? $this->writer->findMaxKValue($pillarForces, 'kMax')
            : null;

        $foundationTable = $context->getResultTable(ResultTableTypeEnum::FOUNDATION);
        $foundKuse = $foundationTable !== null && $foundationTable->isEnabled()
            ? $this->writer->findMaxKValue($foundationTable, 'kUseStability')
            : null;

        $this->writer->equipmentSummaryTable($context, $pillarKuse, $foundKuse);
    }
}
