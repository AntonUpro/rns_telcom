<?php

declare(strict_types=1);

namespace App\Service\DocumentGenerator\Report\Section;

use App\Entity\CalculationResultTable;
use App\Enum\Calculation\ResultTableTypeEnum;
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

    public function build(Section $section, ReportContext $context): void
    {
        $this->tableCounter = 0;

        $this->buildPillarForces($section, $context);
        $this->buildCrackOpening($section, $context);
        $this->buildStressTable($section, $context, ResultTableTypeEnum::BRACE_STRESS, 'напряжения в элементах подкосов', 'СП 16.13330.2017 «Стальные конструкции»');
        $this->buildStressTable($section, $context, ResultTableTypeEnum::PLATFORM_FORCES, 'напряжения в элементах площадки', 'СП 16.13330.2017 «Стальные конструкции»');
        $this->buildStressTable($section, $context, ResultTableTypeEnum::SUPERSTRUCTURE_STRESS, 'напряжения в элементах поясов надстройки', 'СП 16.13330.2017 «Стальные конструкции»');
        $this->buildDeformation($section, $context);
        $this->buildBaseForces($section, $context);
        $this->buildFoundation($section, $context);
        $this->buildSummaryTable($section, $context);
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
            DocStyleRegistry::bodyText(),
            DocStyleRegistry::paragraphLeft(),
        );
        $section->addText('Таблица ' . $num, DocStyleRegistry::normalText(), DocStyleRegistry::paragraphRight());

        $w   = [400, 1200, 1600, 1600, 1600, 1600];
        $tbl = $section->addTable(DocStyleRegistry::tableStyleReport());

        $this->addRow($tbl, $w, ['#', 'Отметка, м', 'Тип опоры', 'Mрасч, тс·м', 'Мдоп, тс·м', 'k(max)'], true);

        foreach ($table->getRows() as $i => $row) {
            $this->addRow($tbl, $w, [
                (string) ($i + 1),
                $this->fmt($row['mark'] ?? null),
                (string) ($row['pillarType'] ?? '—'),
                $this->fmt($row['mCalc'] ?? null, 3),
                $this->fmt($row['mAllowable'] ?? null, 3),
                $this->fmt($row['kMax'] ?? null, 3),
            ]);
        }

        $section->addTextBreak(1);

        $maxRow = $this->findMaxKRow($table, 'kMax');
        if ($maxRow !== null) {
            $comply = ((float) ($maxRow['kMax'] ?? 0)) <= 1.0;
            $section->addText(
                sprintf(
                    'Максимальное усилие в стволе опоры %.3f тс·м при допустимом %.3f тс·м, '
                    . 'Кисп=%d%%, что %s требованиям СП 63.13330.2018;',
                    (float) ($maxRow['mCalc'] ?? 0),
                    (float) ($maxRow['mAllowable'] ?? 0),
                    (int) round((float) ($maxRow['kMax'] ?? 0) * 100),
                    $comply ? 'удовлетворяет' : 'не удовлетворяет',
                ),
                DocStyleRegistry::bodyText(),
                DocStyleRegistry::paragraphLeft(),
            );
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
            DocStyleRegistry::bodyText(),
            DocStyleRegistry::paragraphLeft(),
        );
        $section->addText('Таблица ' . $num, DocStyleRegistry::normalText(), DocStyleRegistry::paragraphRight());

        $w   = [400, 1200, 1600, 2000, 2200, 1600];
        $tbl = $section->addTable(DocStyleRegistry::tableStyleReport());

        $this->addRow($tbl, $w, ['#', 'Отметка, м', 'Тип опоры', 'Расч. ширина трещин, мм', 'Пред. доп. ширина, мм', 'k(max)'], true);

        foreach ($table->getRows() as $i => $row) {
            $this->addRow($tbl, $w, [
                (string) ($i + 1),
                $this->fmt($row['mark'] ?? null),
                (string) ($row['pillarType'] ?? '—'),
                $this->fmt($row['crackWidthCalc'] ?? null, 4),
                $this->fmt($row['crackWidthAllowable'] ?? null, 4),
                $this->fmt($row['kMax'] ?? null, 3),
            ]);
        }

        $section->addTextBreak(1);

        $maxRow = $this->findMaxKRow($table, 'kMax');
        if ($maxRow !== null) {
            $comply = ((float) ($maxRow['kMax'] ?? 0)) <= 1.0;
            $section->addText(
                sprintf(
                    'Максимальное раскрытие трещин в стволе опоры %.4f мм при допустимом %.4f мм, '
                    . 'Кисп=%d%%, что %s требованиям СП 63.13330.2018;',
                    (float) ($maxRow['crackWidthCalc'] ?? 0),
                    (float) ($maxRow['crackWidthAllowable'] ?? 0),
                    (int) round((float) ($maxRow['kMax'] ?? 0) * 100),
                    $comply ? 'удовлетворяет' : 'не удовлетворяет',
                ),
                DocStyleRegistry::bodyText(),
                DocStyleRegistry::paragraphLeft(),
            );
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
        if ($table === null || !$table->isEnabled()) {
            return;
        }

        $num = $this->nextTableNum();
        $section->addText(
            'Максимальные ' . $description . ':',
            DocStyleRegistry::bodyText(),
            DocStyleRegistry::paragraphLeft(),
        );
        $section->addText('Таблица ' . $num, DocStyleRegistry::normalText(), DocStyleRegistry::paragraphRight());

        $w   = [1200, 700, 900, 1100, 700, 700, 700, 700, 700, 700, 700];
        $tbl = $section->addTable(DocStyleRegistry::tableStyleReport());

        $this->addRow($tbl, $w, [
            'Элемент', 'Отм., м', 'Профиль', 'Сечение',
            'A, см²', 'Wy, см³', 'N, тс', 'M, тс·м',
            'Ry, Н/мм²', 'σ, Н/мм²', 'Кисп',
        ], true);

        foreach ($table->getRows() as $row) {
            $this->addRow($tbl, $w, [
                (string) ($row['element'] ?? '—'),
                $this->fmt($row['mark'] ?? null),
                (string) ($row['profileType'] ?? '—'),
                (string) ($row['sectionDesignation'] ?? '—'),
                $this->fmt($row['area'] ?? null, 3),
                $this->fmt($row['momentResistance'] ?? null, 3),
                $this->fmt($row['nCalc'] ?? null, 3),
                $this->fmt($row['mCalc'] ?? null, 3),
                $this->fmt($row['ry'] ?? null, 0),
                $this->fmt($row['sigma'] ?? null, 3),
                $this->fmt($row['kUse'] ?? null, 3),
            ]);
        }

        $section->addTextBreak(1);

        $maxRow = $this->findMaxKRow($table, 'kUse');
        if ($maxRow !== null) {
            $comply = ((float) ($maxRow['kUse'] ?? 0)) <= 1.0;
            $section->addText(
                sprintf(
                    'Максимальное напряжение — %.1f Н/мм² при допустимом %.1f Н/мм², '
                    . 'Кисп=%d%%, что %s требованиям %s;',
                    (float) ($maxRow['sigma'] ?? 0),
                    (float) ($maxRow['ry'] ?? 0),
                    (int) round((float) ($maxRow['kUse'] ?? 0) * 100),
                    $comply ? 'удовлетворяет' : 'не удовлетворяет',
                    $normRef,
                ),
                DocStyleRegistry::bodyText(),
                DocStyleRegistry::paragraphLeft(),
            );
        }

        $section->addTextBreak(1);
    }

    // ─── Деформации ──────────────────────────────────────────────────────────

    private function buildDeformation(Section $section, ReportContext $context): void
    {
        $table = $context->getResultTable(ResultTableTypeEnum::DEFORMATION);
        if ($table === null || !$table->isEnabled()) {
            return;
        }

        $num = $this->nextTableNum();
        $section->addText(
            'Деформации опоры от воздействия ветровых нагрузок:',
            DocStyleRegistry::bodyText(),
            DocStyleRegistry::paragraphLeft(),
        );
        $section->addText('Таблица ' . $num, DocStyleRegistry::normalText(), DocStyleRegistry::paragraphRight());

        $w   = [400, 1200, 1600, 2000, 2200, 1600];
        $tbl = $section->addTable(DocStyleRegistry::tableStyleReport());

        $this->addRow($tbl, $w, [
            '#', 'Отметка, м', 'Перемещение, мм',
            'Верт. угол (max), град.', 'Доп. верт. угол, град.', 'Кисп',
        ], true);

        foreach ($table->getRows() as $i => $row) {
            $this->addRow($tbl, $w, [
                (string) ($i + 1),
                $this->fmt($row['mark'] ?? null),
                $this->fmt($row['displacement'] ?? null, 1),
                $this->fmt($row['angleMax'] ?? null, 4),
                $this->fmt($row['angleAllowable'] ?? null, 4),
                $this->fmt($row['kUse'] ?? null, 3),
            ]);
        }

        $section->addTextBreak(1);

        $maxRow = $this->findMaxKRow($table, 'kUse');
        if ($maxRow !== null) {
            $comply = ((float) ($maxRow['kUse'] ?? 0)) <= 1.0;
            $section->addText(
                sprintf(
                    'Максимальное перемещение верхней отметки опоры от нормативных ветровых нагрузок '
                    . 'составляет %.0f мм, максимальный вертикальный угол отклонения %.4f°. '
                    . 'Кисп=%d%%, что %s нормативным требованиям;',
                    (float) ($maxRow['displacement'] ?? 0),
                    (float) ($maxRow['angleMax'] ?? 0),
                    (int) round((float) ($maxRow['kUse'] ?? 0) * 100),
                    $comply ? 'соответствует' : 'не соответствует',
                ),
                DocStyleRegistry::bodyText(),
                DocStyleRegistry::paragraphLeft(),
            );
        }

        $section->addTextBreak(1);
    }

    // ─── Опорные реакции ──────────────────────────────────────────────────────

    private function buildBaseForces(Section $section, ReportContext $context): void
    {
        $table = $context->getResultTable(ResultTableTypeEnum::BASE_FORCES);
        if ($table === null || !$table->isEnabled()) {
            return;
        }

        $num = $this->nextTableNum();
        $section->addText('Опорные реакции:', DocStyleRegistry::bodyText(), DocStyleRegistry::paragraphLeft());
        $section->addText('Таблица ' . $num, DocStyleRegistry::normalText(), DocStyleRegistry::paragraphRight());

        $w   = [400, 2800, 1600, 1600, 1600];
        $tbl = $section->addTable(DocStyleRegistry::tableStyleReport());

        $this->addRow($tbl, $w, ['#', 'Тип нагрузки', 'N, тс', 'Q, тс', 'М, тс·м'], true);

        foreach ($table->getRows() as $i => $row) {
            $this->addRow($tbl, $w, [
                (string) ($i + 1),
                (string) ($row['loadType'] ?? '—'),
                $this->fmt($row['n'] ?? null, 3),
                $this->fmt($row['q'] ?? null, 3),
                $this->fmt($row['m'] ?? null, 3),
            ]);
        }

        $section->addTextBreak(1);
    }

    // ─── Расчёт основания ────────────────────────────────────────────────────

    private function buildFoundation(Section $section, ReportContext $context): void
    {
        $table = $context->getResultTable(ResultTableTypeEnum::FOUNDATION);
        if ($table === null || !$table->isEnabled()) {
            return;
        }

        $num = $this->nextTableNum();
        $section->addText(
            'Результаты расчёта основания опоры:',
            DocStyleRegistry::bodyText(),
            DocStyleRegistry::paragraphLeft(),
        );
        $section->addText('Таблица ' . $num, DocStyleRegistry::normalText(), DocStyleRegistry::paragraphRight());

        // Двухуровневый заголовок
        $totalW   = 10000;
        $wStab    = [1500, 1500];
        $wDef     = [1500, 1500];
        $wKuse    = [2000, 2000];
        $tbl      = $section->addTable(DocStyleRegistry::tableStyleReport());
        $h        = DocStyleRegistry::headerCell();
        $italic   = DocStyleRegistry::italicCenter();
        $center   = DocStyleRegistry::paragraphCenter();

        // Row 1: group headers
        $tbl->addRow(500);
        $tbl->addCell($wStab[0] + $wStab[1], array_merge($h, ['gridSpan' => 2]))->addText('Устойчивость', $italic, $center);
        $tbl->addCell($wDef[0] + $wDef[1],   array_merge($h, ['gridSpan' => 2]))->addText('Деформации', $italic, $center);
        $tbl->addCell($wKuse[0] + $wKuse[1],  array_merge($h, ['gridSpan' => 2]))->addText('Коэффициент использования', $italic, $center);

        // Row 2: sub-headers
        $tbl->addRow(500);
        $tbl->addCell($wStab[0], $h)->addText('Q, тс', $italic, $center);
        $tbl->addCell($wStab[1], $h)->addText('Qu, тс', $italic, $center);
        $tbl->addCell($wDef[0],  $h)->addText('β, рад.', $italic, $center);
        $tbl->addCell($wDef[1],  $h)->addText('βu, рад.', $italic, $center);
        $tbl->addCell($wKuse[0], $h)->addText('Расч. на устойч.', $italic, $center);
        $tbl->addCell($wKuse[1], $h)->addText('Расч. по деф.', $italic, $center);

        // Data rows
        $dc = DocStyleRegistry::dataCell();
        $c  = DocStyleRegistry::center();

        foreach ($table->getRows() as $row) {
            $tbl->addRow(400);
            $tbl->addCell($wStab[0], $dc)->addText($this->fmt($row['q'] ?? null, 3), $c, $center);
            $tbl->addCell($wStab[1], $dc)->addText($this->fmt($row['qU'] ?? null, 3), $c, $center);
            $tbl->addCell($wDef[0],  $dc)->addText($this->fmt($row['beta'] ?? null, 4), $c, $center);
            $tbl->addCell($wDef[1],  $dc)->addText($this->fmt($row['betaU'] ?? null, 4), $c, $center);
            $tbl->addCell($wKuse[0], $dc)->addText($this->fmt($row['kUseStability'] ?? null, 3), $c, $center);
            $tbl->addCell($wKuse[1], $dc)->addText($this->fmt($row['kUseDeformation'] ?? null, 3), $c, $center);
        }

        $section->addTextBreak(1);

        // Summary per row type
        foreach ($table->getRows() as $row) {
            $ks = $row['kUseStability'] ?? null;
            $kd = $row['kUseDeformation'] ?? null;
            if ($ks !== null) {
                $comply = (float) $ks <= 1.0;
                $section->addText(
                    sprintf(
                        'Расчёт на устойчивость стойки в грунте: Кисп=%.3f — %s требованиям;',
                        (float) $ks,
                        $comply ? 'соответствует' : 'не соответствует',
                    ),
                    DocStyleRegistry::bodyText(),
                    DocStyleRegistry::paragraphLeft(),
                );
            }
            if ($kd !== null) {
                $comply = (float) $kd <= 1.0;
                $section->addText(
                    sprintf(
                        'Расчёт по деформациям: Кисп=%.3f — %s требованиям.',
                        (float) $kd,
                        $comply ? 'соответствует' : 'не соответствует',
                    ),
                    DocStyleRegistry::bodyText(),
                    DocStyleRegistry::paragraphLeft(),
                );
            }
        }

        $section->addTextBreak(1);
    }

    // ─── Сводная таблица ──────────────────────────────────────────────────────

    private function buildSummaryTable(Section $section, ReportContext $context): void
    {
        $num = $this->nextTableNum();
        $section->addText('Таблица ' . $num, DocStyleRegistry::normalText(), DocStyleRegistry::paragraphRight());

        $w   = [6000, 4000];
        $tbl = $section->addTable(DocStyleRegistry::tableStyleReport());

        $italic  = DocStyleRegistry::italicCenter();
        $center  = DocStyleRegistry::paragraphCenter();
        $left    = DocStyleRegistry::paragraphLeft();
        $h       = DocStyleRegistry::headerCell();
        $dc      = DocStyleRegistry::dataCell();
        $c       = DocStyleRegistry::center();

        $tbl->addRow(500);
        $tbl->addCell($w[0], $h)->addText('Показатель', $italic, $left);
        $tbl->addCell($w[1], $h)->addText('Значение', $italic, $center);

        $pillarForces = $context->getResultTable(ResultTableTypeEnum::PILLAR_FORCES);
        $pillarKuse   = $pillarForces !== null
            ? $this->findMaxKValue($pillarForces, 'kMax')
            : null;

        $foundationTable = $context->getResultTable(ResultTableTypeEnum::FOUNDATION);
        $foundKuse       = $foundationTable !== null && $foundationTable->isEnabled()
            ? $this->findMaxKValue($foundationTable, 'kUseStability')
            : null;

        $rows = [
            ['Коэффициент использования несущей способности конструкций опоры', $this->fmt($pillarKuse, 3)],
            ['Коэффициент использования фундамента', $this->fmt($foundKuse, 3)],
        ];

        foreach ($rows as [$label, $value]) {
            $tbl->addRow(400);
            $tbl->addCell($w[0], $dc)->addText($label, $c, $left);
            $tbl->addCell($w[1], $dc)->addText($value, $c, $center);
        }

        $section->addTextBreak(1);
    }

    // ─── Вспомогательные методы ───────────────────────────────────────────────

    private function nextTableNum(): int
    {
        return ++$this->tableCounter;
    }

    private function addRow(Table $table, array $widths, array $values, bool $isHeader = false): void
    {
        $table->addRow($isHeader ? 500 : 400);
        $style  = $isHeader ? DocStyleRegistry::headerCell() : DocStyleRegistry::dataCell();
        $font   = $isHeader ? DocStyleRegistry::italicCenter() : DocStyleRegistry::center();
        $para   = DocStyleRegistry::paragraphCenter();

        foreach ($values as $i => $value) {
            $table->addCell($widths[$i] ?? 1000, $style)->addText($value, $font, $para);
        }
    }

    private function findMaxKRow(CalculationResultTable $table, string $field): ?array
    {
        $maxRow = null;
        $maxK   = null;
        foreach ($table->getRows() as $row) {
            $k = isset($row[$field]) ? (float) $row[$field] : null;
            if ($k !== null && ($maxK === null || $k > $maxK)) {
                $maxK   = $k;
                $maxRow = $row;
            }
        }
        return $maxRow;
    }

    private function findMaxKValue(CalculationResultTable $table, string $field): ?float
    {
        $max = null;
        foreach ($table->getRows() as $row) {
            $k = isset($row[$field]) ? (float) $row[$field] : null;
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
        return number_format((float) $value, $decimals, ',', ' ');
    }
}
