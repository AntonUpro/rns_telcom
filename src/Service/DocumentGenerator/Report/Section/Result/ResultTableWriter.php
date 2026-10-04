<?php

declare(strict_types=1);

namespace App\Service\DocumentGenerator\Report\Section\Result;

use App\Entity\CalculationResultTable;
use App\Enum\Calculation\ResultTableTypeEnum;
use App\Enum\Equipment\EquipmentGroupEnum;
use App\Enum\Gauge\GaugeProfileTypeEnum;
use App\Service\DocumentGenerator\DocStyleRegistry;
use App\Service\DocumentGenerator\Report\ReportContext;
use PhpOffice\PhpWord\Element\Section;
use PhpOffice\PhpWord\Element\Table;
use PhpOffice\PhpWord\SimpleType\Jc;

/**
 * Общие строительные блоки раздела «Результаты расчёта и выводы»:
 * подписи «Таблица N», строки таблиц, абзацы-выводы и таблица устойчивости.
 *
 * Создаётся на время построения одного раздела и ведёт сквозную нумерацию таблиц.
 */
final class ResultTableWriter
{
    private const STEEL_NORM = 'СП 16.13330.2017 «Стальные конструкции»';

    public function __construct(
        private readonly Section $section,
        private int $tableNum,
    ) {
    }

    /** Номер последней добавленной таблицы */
    public function tableNum(): int
    {
        return $this->tableNum;
    }

    public function nextTableNum(): int
    {
        return ++$this->tableNum;
    }

    /**
     * Подчёркнутый заголовок таблицы и подпись «Таблица N» справа.
     */
    public function caption(string $title): void
    {
        $num = $this->nextTableNum();
        $this->section->addText(
            $title,
            DocStyleRegistry::titleTableTextUnderline(),
            DocStyleRegistry::paragraphIndentWithKeepNext(),
        );
        $this->section->addText('Таблица ' . $num, DocStyleRegistry::normalText(), DocStyleRegistry::paragraphRight());
    }

    public function addRow(Table $table, array $widths, array $values, bool $isHeader = false, bool $keepWithNext = true): void
    {
        $table->addRow($isHeader ? 500 : 400, ['cantSplit' => true]);
        $style = $isHeader ? DocStyleRegistry::headerCell() : DocStyleRegistry::dataCell();
        $font = $isHeader ? DocStyleRegistry::italicCenter() : DocStyleRegistry::center();
        $para = DocStyleRegistry::paragraphCenter();
        if ($keepWithNext) {
            $para['keepNext'] = true;
        }

        foreach ($values as $i => $value) {
            $table->addCell($widths[$i] ?? 1000, $style)->addText($value, $font, $para);
        }
    }

    /**
     * Таблица устойчивости стальных элементов (пояса / раскосы / распорки).
     * Строки — {@see \App\Dto\Calculation\CalculationResult\Row\SuperstructureStabilityRowDto}.
     */
    public function stabilityTable(ReportContext $context, ResultTableTypeEnum $type, string $description): void
    {
        $table = $context->getResultTable($type);
        if ($table === null || ! $table->isEnabled()) {
            return;
        }

        $this->caption('Максимальные ' . $description . ':');

        $w = [700, 1000, 1000, 900, 900, 700, 700, 700, 700, 700, 700, 700, 500];
        $tbl = $this->section->addTable(DocStyleRegistry::tableStyleReport());

        $rows = $table->getRows();
        $last = count($rows) - 1;

        $this->addRow($tbl, $w, [
            'Номер секции', 'Отметка верха, м', 'Сечение',
            'Момент инерции I, см⁴', 'Радиус инерции i, см', 'λ', 'Lef, см', 'ϕ',
            'N, тс', 'Nmax, тс', 'σ, Н/мм²', 'Ry, Н/мм²', 'Кисп',
        ], true, $last >= 0);

        foreach ($rows as $i => $row) {
            $this->addRow($tbl, $w, [
                $this->fmt($row['sectionNumber'] ?? null, 0),
                $this->fmt($row['mark'] ?? null, 3),
                $row['profileType']
                    ? GaugeProfileTypeEnum::from($row['profileType'])->icon() . ($row['sectionDesignation'] ?? '—')
                    : '—',
                $this->fmt($row['momentInertia'] ?? null, 2),
                $this->fmt($row['radiusInertia'] ?? null, 2),
                $this->fmt($row['lambda'] ?? null, 2),
                $this->fmt($row['elementLength'] ?? null, 1),
                $this->fmt($row['fi'] ?? null, 3),
                $this->fmt($row['nCalc'] ?? null, 2),
                $this->fmt($row['nMax'] ?? null, 2),
                $this->fmt($row['sigma'] ?? null, 0),
                $this->fmt($row['ry'] ?? null, 0),
                $this->fmt($row['kUse'] ?? null, 2),
            ], false, $i < $last);
        }

        $this->stressVerdict($this->findMaxKRow($table, 'kUse'), $description);

        $this->section->addTextBreak(1);
    }

    /**
     * Абзац «Максимальное <description> составляет σ при допустимом Ry, Kисп=…%,
     * что (не) удовлетворяет требованиям СП 16».
     *
     * @param string $limitField поле строки с допустимым напряжением (Ry, Rbt, Rbs…)
     */
    public function stressVerdict(?array $maxRow, string $description, string $limitField = 'ry'): void
    {
        if ($maxRow === null) {
            return;
        }

        $kUse = (float)($maxRow['kUse'] ?? 0);
        $comply = $kUse <= 1.0;
        $style = $this->verdictStyle($comply);
        $text = $this->section->addTextRun(DocStyleRegistry::paragraphIndent());

        $text->addText('Максимальное ' . $description . ' составляет ', DocStyleRegistry::bodyText());
        $text->addText(sprintf('%.0f Н/мм²', (float)($maxRow['sigma'] ?? 0)), $style);
        $text->addText(' при допустимом ', DocStyleRegistry::bodyText());
        $text->addText(sprintf('%.0f Н/мм²', (float)($maxRow[$limitField] ?? 0)), DocStyleRegistry::titleTableTextUnderline());
        $text->addText(', ', DocStyleRegistry::bodyText());
        $text->addText(sprintf('Kисп=%.0f', $kUse * 100) . '%', $style);
        $text->addText(', что ', DocStyleRegistry::bodyText());
        $text->addText($comply ? 'удовлетворяет' : 'не удовлетворяет', $style);
        $text->addText(' требованиям ' . self::STEEL_NORM . ';', DocStyleRegistry::bodyText());
    }

    /**
     * Сводная таблица: Кисп конструкций и фундаментов, площадь и вес оборудования,
     * максимально допустимые площадь и вес (пропорционально запасу несущей способности).
     */
    public function equipmentSummaryTable(ReportContext $context, ?float $constructionKuse, ?float $foundationKuse): void
    {
        $num = $this->nextTableNum();
        $this->section->addText('Таблица ' . $num, DocStyleRegistry::normalText(), DocStyleRegistry::paragraphRight());

        $w = [6000, 4000];
        $tbl = $this->section->addTable(DocStyleRegistry::tableStyleReport());

        $italic = DocStyleRegistry::italicCenter();
        $center = array_merge(DocStyleRegistry::paragraphCenter(), DocStyleRegistry::paragraphLineSpacing());
        $left = array_merge(['alignment' => Jc::START], DocStyleRegistry::paragraphLineSpacing());
        $dc = DocStyleRegistry::dataCell();

        $areaEquipment = 0;
        $weightEquipment = 0;
        foreach ($context->calculation->getCalculationEquipments() as $equipment) {
            if ($equipment->getEquipmentGroup() === EquipmentGroupEnum::EXIST || $equipment->getEquipmentGroup() === EquipmentGroupEnum::DISMANT) {
                $areaEquipment += $equipment->getEquipmentParams()['height'] / 1000 * $equipment->getEquipmentParams()['width'] / 1000 * $equipment->getQuantity();
                $weightEquipment += $equipment->getEquipmentParams()['weight'] * $equipment->getQuantity();
            }
        }

        $maxKUse = max($constructionKuse, $foundationKuse);
        $allowable = fn(float $value): string => $maxKUse ? $this->fmt($value / $maxKUse, 2) : '—';
        $fmtPercent = fn(?float $k): string => $k !== null ? $this->fmt($k * 100, 0) : '—';

        $rows = [
            ['Коэффициент использования конструкций (по наиболее нагруженному элементу), %', $fmtPercent($constructionKuse)],
            ['Коэффициент использования фундаментов, %', $fmtPercent($foundationKuse)],
            ['Площадь оборудования на момент расчета, м²', $this->fmt($areaEquipment, 2)],
            ['Вес оборудования на момент расчета, кг', $this->fmt($weightEquipment, 2)],
            ['Максимально допустимая площадь оборудования (ориентировочно относительно отметок подвеса существующего оборудования), м²', $allowable($areaEquipment)],
            ['Максимально допустимый вес оборудования на АМС, кг', $allowable($weightEquipment)],
        ];

        $last = count($rows) - 1;
        $leftKeep = array_merge($left, ['keepNext' => true]);
        $centerKeep = array_merge($center, ['keepNext' => true]);

        foreach ($rows as $i => [$label, $value]) {
            $tbl->addRow(400, ['cantSplit' => true]);
            $tbl->addCell($w[0], $dc)->addText($label, $italic, $i < $last ? $leftKeep : $left);
            $tbl->addCell($w[1], $dc)->addText($value, $italic, $i < $last ? $centerKeep : $center);
        }
    }

    /** Подчёркнутый стиль для выполненного условия, жирный подчёркнутый — для невыполненного */
    public function verdictStyle(bool $comply): array
    {
        return $comply
            ? DocStyleRegistry::titleTableTextUnderline()
            : DocStyleRegistry::titleTableTextUnderlineBold();
    }

    public function findMaxKRow(CalculationResultTable $table, string $field): ?array
    {
        return $this->findMaxKRowFromRows($table->getRows(), $field);
    }

    public function findMaxKRowFromRows(array $rows, string $field): ?array
    {
        $maxRow = null;
        $maxK = null;
        foreach ($rows as $row) {
            $k = isset($row[$field]) ? (float)$row[$field] : null;
            if ($k !== null && ($maxK === null || $k > $maxK)) {
                $maxK = $k;
                $maxRow = $row;
            }
        }
        return $maxRow;
    }

    public function findMaxKValue(CalculationResultTable $table, string $field): ?float
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

    public function fmt(mixed $value, int $decimals = 2): string
    {
        if ($value === null || $value === '') {
            return '—';
        }
        return number_format((float)$value, $decimals, ',', ' ');
    }
}
