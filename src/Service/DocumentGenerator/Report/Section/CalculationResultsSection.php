<?php

declare(strict_types=1);

namespace App\Service\DocumentGenerator\Report\Section;

use App\Entity\CalculationResultTable;
use App\Enum\Calculation\ResultTableTypeEnum;
use App\Enum\Equipment\EquipmentGroupEnum;
use App\Enum\Gauge\GaugeProfileTypeEnum;
use App\Service\DocumentGenerator\DocStyleRegistry;
use App\Service\DocumentGenerator\Report\ReportContext;
use App\Service\DocumentGenerator\Report\SectionBuilderInterface;
use PhpOffice\PhpWord\Element\Section;
use PhpOffice\PhpWord\Element\Table;

/**
 * Раздел «Результаты расчёта и выводы».
 * Строит таблицы по каждому типу из calculation_result_table.
 */
final class CalculationResultsSection implements SectionBuilderInterface
{
    private int $tableCounter = 0;

    public function build(Section $section, ReportContext $context, int &$tableNum): void
    {
        $this->tableCounter = $tableNum;

        $this->buildPillarForces($section, $context);
        $this->buildStressTable($section, $context, ResultTableTypeEnum::BRACE_STRESS, 'напряжения в элементах подкосов', 'СП 16.13330.2017 «Стальные конструкции»');
        $this->buildStressTable($section, $context, ResultTableTypeEnum::PLATFORM_FORCES, 'напряжения в элементах площадки', 'СП 16.13330.2017 «Стальные конструкции»');
        $this->buildStressTable($section, $context, ResultTableTypeEnum::SUPERSTRUCTURE_STRESS, 'напряжения в элементах поясов надстройки', 'СП 16.13330.2017 «Стальные конструкции»');
        $this->buildDeformation($section, $context);
        $this->buildBaseForces($section, $context);
        $this->buildFoundation($section, $context);
        $this->buildSummaryTable($section, $context);

        $tableNum = $this->tableCounter;
    }

    // ─── Усилия в стволе опоры ────────────────────────────────────────────────

    private function buildPillarForces(Section $section, ReportContext $context): void
    {
        $table = $context->getResultTable(ResultTableTypeEnum::PILLAR_FORCES);
        if ($table === null) {
            return;
        }

        $num = $this->nextTableNum();
        $section->addText(
            'Максимальные усилия в стволе опоры от расчётных нагрузок:',
            DocStyleRegistry::titleTableTextUnderline(),
            DocStyleRegistry::paragraphIndent(),
        );
        $section->addText('Таблица ' . $num, DocStyleRegistry::normalText(), DocStyleRegistry::paragraphRight());

        $w = [400, 1600, 2100, 2000, 2000, 1900];
        $tbl = $section->addTable(DocStyleRegistry::tableStyleReport());

        $this->addRow($tbl, $w, ['№', 'Отметка, м', 'Тип опоры', 'Mрасч, тс·м', 'Мдоп, тс·м', 'Кисп'], true);

        foreach ($table->getRows() as $i => $row) {
            $this->addRow($tbl, $w, [
                (string)($i + 1),
                $this->fmt($row['mark'] ?? null),
                (string)($row['pillarType'] ?? '—'),
                $this->fmt($row['mCalc'] ?? null, 3),
                $this->fmt($row['mAllowable'] ?? null, 3),
                $this->fmt($row['kMax'] ?? null, 3),
            ]);
        }

        $maxRow = $this->findMaxKRow($table, 'kMax');
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

    // ─── Раскрытие трещин ────────────────────────────────────────────────────

    private function buildCrackOpening(Section $section, ReportContext $context): void
    {
        $table = $context->getResultTable(ResultTableTypeEnum::CRACK_OPENING);
        if ($table === null) {
            return;
        }

        $num = $this->nextTableNum();
        $section->addText(
            'Максимальное раскрытие трещин в стволе опоры от нормативных нагрузок:',
            DocStyleRegistry::titleTableTextUnderline(),
            DocStyleRegistry::paragraphIndent(),
        );
        $section->addText('Таблица ' . $num, DocStyleRegistry::normalText(), DocStyleRegistry::paragraphRight());

        $w = [400, 1300, 1600, 2100, 2300, 1700];
        $tbl = $section->addTable(DocStyleRegistry::tableStyleReport());

        $this->addRow($tbl, $w, ['№', 'Отметка, м', 'Тип опоры', 'Расч. ширина трещин, мм', 'Пред. доп. ширина, мм', 'k(max)'], true);

        foreach ($table->getRows() as $i => $row) {
            $this->addRow($tbl, $w, [
                (string)($i + 1),
                $this->fmt($row['mark'] ?? null),
                (string)($row['pillarType'] ?? '—'),
                $this->fmt($row['crackWidthCalc'] ?? null, 4),
                $this->fmt($row['crackWidthAllowable'] ?? null, 4),
                $this->fmt($row['kMax'] ?? null, 3),
            ]);
        }

        $maxRow = $this->findMaxKRow($table, 'kMax');
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

        $num = $this->nextTableNum();
        $section->addText(
            'Максимальные ' . $description . ':',
            DocStyleRegistry::titleTableTextUnderline(),
            DocStyleRegistry::paragraphIndent(),
        );
        $section->addText('Таблица ' . $num, DocStyleRegistry::normalText(), DocStyleRegistry::paragraphRight());

        $w = [1200, 1200, 1200, 1000, 900, 900, 900, 900, 900, 900];
        $tbl = $section->addTable(DocStyleRegistry::tableStyleReport());

        $this->addRow($tbl, $w, [
            'Отм., м', 'Элемент', 'Сечение',
            'A, см²', 'Wy, см³', 'N, тс', 'M, тс·м',
            'Ry, Н/мм²', 'σ, Н/мм²', 'Кисп',
        ], true);

        foreach ($table->getRows() as $row) {
            $this->addRow($tbl, $w, [
                $this->fmt($row['mark'] ?? null, 3),
                (string)($row['element'] ?? '—'),
//                $row['profileType'] ? GaugeProfileTypeEnum::from($row['profileType'])->label() : '—',
                GaugeProfileTypeEnum::from($row['profileType'])->icon() . ($row['sectionDesignation'] ?? '—'),
                $this->fmt($row['area'] ?? null, 2),
                $this->fmt($row['momentResistance'] ?? null, 2),
                $this->fmt($row['nCalc'] ?? null, 2),
                $this->fmt($row['mCalc'] ?? null, 2),
                $this->fmt($row['sigma'] ?? null, 0),
                $this->fmt($row['ry'] ?? null, 0),
                $this->fmt($row['kUse'] ?? null, 2),
            ]);
        }

        $maxRow = $this->findMaxKRow($table, 'kUse');
        if ($maxRow !== null) {
            $comply = ((float)($maxRow['kUse'] ?? 0)) <= 1.0;

            $style = $comply ? DocStyleRegistry::titleTableTextUnderline() : DocStyleRegistry::titleTableTextUnderlineBold();
            $text = $section->addTextRun(DocStyleRegistry::paragraphIndent());

            $text->addText('Максимальное ' . $description . ' составляет ', DocStyleRegistry::bodyText());
            $text->addText(sprintf('%.0f Н/мм²', (float)($maxRow['sigma'] ?? 0)), $style);
            $text->addText(' при допустимом ', DocStyleRegistry::bodyText());
            $text->addText(sprintf('%.0f Н/мм²', (float)($maxRow['ry'] ?? 0)), DocStyleRegistry::titleTableTextUnderline());
            $text->addText(', ', DocStyleRegistry::bodyText());
            $text->addText(sprintf('Kисп=%.0f', (float)($maxRow['kUse'] ?? 0) * 100) . '%', $style);
            $text->addText(', что ', DocStyleRegistry::bodyText());
            $text->addText($comply ? 'удовлетворяет' : 'не удовлетворяет', $style);
            $text->addText(' требованиям СП 16.13330.2017 «Стальные конструкции»;', DocStyleRegistry::bodyText());
        }

        $section->addTextBreak(1);
    }

    // ─── Деформации ──────────────────────────────────────────────────────────

    private function buildDeformation(Section $section, ReportContext $context): void
    {
        $table = $context->getResultTable(ResultTableTypeEnum::DEFORMATION);
        if ($table === null || ! $table->isEnabled()) {
            return;
        }

        $num = $this->nextTableNum();
        $section->addText(
            'Деформации опоры от воздействия ветровых нагрузок:',
            DocStyleRegistry::titleTableTextUnderline(),
            DocStyleRegistry::paragraphIndent(),
        );
        $section->addText('Таблица ' . $num, DocStyleRegistry::normalText(), DocStyleRegistry::paragraphRight());

        $w = [400, 1600, 2000, 2000, 2000, 2000];
        $tbl = $section->addTable(DocStyleRegistry::tableStyleReport());

        $this->addRow($tbl, $w, [
            '№', 'Отметка, м', 'Перемещение, мм',
            'Верт. угол (max), град.', 'Допустимый вертикальный угол, град.', 'Кисп',
        ], true);

        foreach ($table->getRows() as $i => $row) {
            $this->addRow($tbl, $w, [
                (string)($i + 1),
                $this->fmt($row['mark'] ?? null),
                $this->fmt($row['displacement'] ?? null, 1),
                $this->fmt($row['angleMax'] ?? null, 2),
                $this->fmt($row['angleAllowable'] ?? null, 2),
                $this->fmt($row['kUse'] ?? null, 2),
            ]);
        }

        $maxRow = $this->findMaxKRow($table, 'kUse');
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

        $num = $this->nextTableNum();
        $section->addText('Расчетные нагрузки, возникающие в уровне заделки стойки:', DocStyleRegistry::titleTableTextUnderline(), DocStyleRegistry::paragraphIndent());
        $section->addText('Таблица ' . $num, DocStyleRegistry::normalText(), DocStyleRegistry::paragraphRight());

        $w = [400, 3600, 2000, 2000, 2000];
        $tbl = $section->addTable(DocStyleRegistry::tableStyleReport());

        $this->addRow($tbl, $w, ['№', 'Тип нагрузки', 'N, тс', 'Q, тс', 'М, тс·м'], true);

        foreach ($table->getRows() as $i => $row) {
            $this->addRow($tbl, $w, [
                (string)($i + 1),
                (string)($row['loadType'] ?? '—'),
                $this->fmt($row['n'] ?? null, 1),
                $this->fmt($row['q'] ?? null, 1),
                $this->fmt($row['m'] ?? null, 1),
            ]);
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

        $num = $this->nextTableNum();
        $section->addText(
            'Результаты расчёта основания опоры:',
            DocStyleRegistry::titleTableTextUnderline(),
            DocStyleRegistry::paragraphIndent(),
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

        // Row 1: group headers
        $tbl->addRow(500);
        $tbl->addCell($wStab[0] + $wStab[1], array_merge($h, ['gridSpan' => 2]))->addText('Устойчивость', $italic, $center);
        $tbl->addCell($wDef[0] + $wDef[1], array_merge($h, ['gridSpan' => 2]))->addText('Деформации', $italic, $center);
        $tbl->addCell($wKuse[0] + $wKuse[1], array_merge($h, ['gridSpan' => 2]))->addText('Коэффициент использования', $italic, $center);

        // Row 2: sub-headers
        $tbl->addRow(500);
        $tbl->addCell($wStab[0], $h)->addText('Q, тс', $italic, $center);
        $tbl->addCell($wStab[1], $h)->addText('Qu, тс', $italic, $center);
        $tbl->addCell($wDef[0], $h)->addText('β, рад.', $italic, $center);
        $tbl->addCell($wDef[1], $h)->addText('βu, рад.', $italic, $center);
        $tbl->addCell($wKuse[0], $h)->addText('Расч. на устойч.', $italic, $center);
        $tbl->addCell($wKuse[1], $h)->addText('Расч. по деф.', $italic, $center);

        // Data rows
        $dc = DocStyleRegistry::dataCell();
        $c = DocStyleRegistry::center();

        foreach ($table->getRows() as $row) {
            $tbl->addRow(400);
            $tbl->addCell($wStab[0], $dc)->addText($this->fmt($row['q'] ?? null, 3), $c, $center);
            $tbl->addCell($wStab[1], $dc)->addText($this->fmt($row['qU'] ?? null, 3), $c, $center);
            $tbl->addCell($wDef[0], $dc)->addText($this->fmt($row['beta'] ?? null, 4), $c, $center);
            $tbl->addCell($wDef[1], $dc)->addText($this->fmt($row['betaU'] ?? null, 4), $c, $center);
            $tbl->addCell($wKuse[0], $dc)->addText($this->fmt($row['kUseStability'] ?? null, 3), $c, $center);
            $tbl->addCell($wKuse[1], $dc)->addText($this->fmt($row['kUseDeformation'] ?? null, 3), $c, $center);
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
        $num = $this->nextTableNum();
        $section->addText('Таблица ' . $num, DocStyleRegistry::normalText(), DocStyleRegistry::paragraphRight());

        $w = [6000, 4000];
        $tbl = $section->addTable(DocStyleRegistry::tableStyleReport());

        $italic = DocStyleRegistry::italicCenter();
        $center = array_merge(DocStyleRegistry::paragraphCenter(), DocStyleRegistry::paragraphLineSpacing());
        $left = array_merge(DocStyleRegistry::paragraphLeft(), DocStyleRegistry::paragraphLineSpacing());
        $dc = DocStyleRegistry::dataCell();
        $c = DocStyleRegistry::center();

        $pillarForces = $context->getResultTable(ResultTableTypeEnum::PILLAR_FORCES);
        $pillarKuse = $pillarForces !== null
            ? $this->findMaxKValue($pillarForces, 'kMax')
            : null;

        $foundationTable = $context->getResultTable(ResultTableTypeEnum::FOUNDATION);
        $foundKuse = $foundationTable !== null && $foundationTable->isEnabled()
            ? $this->findMaxKValue($foundationTable, 'kUseStability')
            : null;

        $areaEquipment = 0;
        $weightEquipment = 0;
        foreach ($context->calculation->getCalculationEquipments() as $equipment) {
            if ($equipment->getEquipmentGroup() === EquipmentGroupEnum::EXIST || $equipment->getEquipmentGroup() === EquipmentGroupEnum::DISMANT) {
                $areaEquipment += $equipment->getEquipmentParams()['height'] / 1000 * $equipment->getEquipmentParams()['width'] / 1000 * $equipment->getQuantity();
                $weightEquipment += $equipment->getEquipmentParams()['weight'] * $equipment->getQuantity();
            }
        };

        $maxKUse = max($pillarKuse, $foundKuse);

        $rows = [
            ['Коэффициент использования конструкций (по наиболее нагруженному элементу)', $this->fmt($pillarKuse, 2)],
            ['Коэффициент использования фундаментов', $this->fmt($foundKuse, 2)],
            ['Площадь оборудования на момент расчета', $this->fmt($areaEquipment, 2)],
            ['Вес оборудования на момент расчета, кг', $this->fmt($weightEquipment, 2)],
            ['Максимально допустимая площадь оборудования (ориентировочно относительно отметок подвеса существующего оборудования)', $this->fmt($areaEquipment / $maxKUse, 2)],
            ['Максимально допустимый вес оборудования на АМС, кг', $this->fmt($weightEquipment / $maxKUse, 2)],
        ];

        foreach ($rows as [$label, $value]) {
            $tbl->addRow(400);
            $tbl->addCell($w[0], $dc)->addText($label, $italic, $left);
            $tbl->addCell($w[1], $dc)->addText($value, $italic, $center);
        }
    }

    // ─── Вспомогательные методы ───────────────────────────────────────────────

    private function nextTableNum(): int
    {
        return ++$this->tableCounter;
    }

    private function addRow(Table $table, array $widths, array $values, bool $isHeader = false): void
    {
        $table->addRow($isHeader ? 500 : 400);
        $style = $isHeader ? DocStyleRegistry::headerCell() : DocStyleRegistry::dataCell();
        $font = $isHeader ? DocStyleRegistry::italicCenter() : DocStyleRegistry::center();
        $para = DocStyleRegistry::paragraphCenter();

        foreach ($values as $i => $value) {
            $table->addCell($widths[$i] ?? 1000, $style)->addText($value, $font, $para);
        }
    }

    private function findMaxKRow(CalculationResultTable $table, string $field): ?array
    {
        $maxRow = null;
        $maxK = null;
        foreach ($table->getRows() as $row) {
            $k = isset($row[$field]) ? (float)$row[$field] : null;
            if ($k !== null && ($maxK === null || $k > $maxK)) {
                $maxK = $k;
                $maxRow = $row;
            }
        }
        return $maxRow;
    }

    private function findMaxKValue(CalculationResultTable $table, string $field): ?float
    {
        $max = null;
        foreach ($table->getRows() as $row) {
            $k = isset($row[$field]) ? (float)$row[$field] : null;
            if ($k !== null && ($max === null || $k > $max)) {
                $max = $k;
            }
        }
        return $max;
    }

    private function fmt(mixed $value, int $decimals = 2): string
    {
        if ($value === null || $value === '') {
            return '—';
        }
        return number_format((float)$value, $decimals, ',', ' ');
    }
}
