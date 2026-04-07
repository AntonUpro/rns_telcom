<?php

declare(strict_types=1);

namespace App\Service\DocumentGenerator\Table;

use App\Dto\Calculation\TotalLoad\EquipmentHeightTotalLoadDto;
use App\Dto\Calculation\TotalLoad\PillarSectionTotalLoadDto;
use App\Dto\Calculation\TotalLoad\PlatformSectionTotalLoadDto;
use App\Dto\Calculation\TotalLoad\TotalLoadResponseDto;
use App\Service\DocumentGenerator\DocStyleRegistry;
use PhpOffice\PhpWord\Element\Section;
use PhpOffice\PhpWord\Element\Table;

/**
 * Строит три таблицы суммарной ветровой нагрузки.
 *
 * Таблица 5а — ствол опоры + коммуникации по секциям:
 *   № секции | Верхняя отметка, м | Высота секции, м | Суммарная нагрузка, кг | кг/м
 *
 * Таблица 5б — площадка и подкосы:
 *   Элемент | Верхняя отметка, м | Высота, м | Суммарная нагрузка, кг | кг/(м·пояс)
 *
 * Таблица 5в — оборудование по высотным группам:
 *   № группы | Высотная отметка, мм | Суммарная нагрузка, кг
 */
final class TotalLoadTableBuilder
{
    // ─── Ширины столбцов ──────────────────────────────────────────────────────

    private const PILLAR_WIDTHS   = [700, 1400, 1400, 1600, 1600];
    private const PLATFORM_WIDTHS = [1400, 1400, 1400, 1600, 1600];
    private const EQUIPMENT_WIDTHS = [1000, 1600, 1600];

    public function build(Section $section, TotalLoadResponseDto $data): void
    {
        $section->addText('Суммарная ветровая нагрузка', DocStyleRegistry::sectionTitle(), DocStyleRegistry::paragraphTitle());
        $section->addTextBreak(1);

        $this->buildPillarTable($section, $data);
        $section->addTextBreak(1);

        $this->buildPlatformTable($section, $data);
        $section->addTextBreak(1);

        $this->buildEquipmentTable($section, $data);
    }

    // ─── Таблица 5а: ствол + коммуникации ────────────────────────────────────

    private function buildPillarTable(Section $section, TotalLoadResponseDto $data): void
    {
        $section->addText('5а. Нагрузка на ствол опоры и коммуникации', DocStyleRegistry::bold(), DocStyleRegistry::paragraphLeft());
        $section->addTextBreak(1);

        $table = $section->addTable(DocStyleRegistry::tableStyle());

        $this->addPillarHeader($table);

        $arr = $data->toArray();
        foreach ($arr['pillarSections'] as $row) {
            $this->addPillarRow($table, $row);
        }
    }

    private function addPillarHeader(Table $table): void
    {
        $bold   = DocStyleRegistry::boldCenter();
        $center = DocStyleRegistry::paragraphCenter();
        $hCell  = DocStyleRegistry::headerCell();
        $w      = self::PILLAR_WIDTHS;

        $table->addRow(600);
        $table->addCell($w[0], $hCell)->addText('№ секции', $bold, $center);
        $table->addCell($w[1], $hCell)->addText('Верхняя отметка, м', $bold, $center);
        $table->addCell($w[2], $hCell)->addText('Высота секции, м', $bold, $center);
        $table->addCell($w[3], $hCell)->addText('Суммарная нагрузка, кг', $bold, $center);
        $table->addCell($w[4], $hCell)->addText('Нагрузка, кг/м', $bold, $center);
    }

    private function addPillarRow(Table $table, array $row): void
    {
        $c      = DocStyleRegistry::center();
        $center = DocStyleRegistry::paragraphCenter();
        $dc     = DocStyleRegistry::dataCell();
        $w      = self::PILLAR_WIDTHS;

        $table->addRow(400);
        $table->addCell($w[0], $dc)->addText((string)$row['sectionNumber'], $c, $center);
        $table->addCell($w[1], $dc)->addText($this->fmt($row['topHeight'] / 1000), $c, $center);
        $table->addCell($w[2], $dc)->addText($this->fmt($row['sectionHeight'] / 1000), $c, $center);
        $table->addCell($w[3], $dc)->addText($this->fmt($row['totalLoad'], 1), $c, $center);
        $table->addCell($w[4], $dc)->addText($this->fmt($row['loadPerLinearMeter'], 1), $c, $center);
    }

    // ─── Таблица 5б: площадка и подкосы ──────────────────────────────────────

    private function buildPlatformTable(Section $section, TotalLoadResponseDto $data): void
    {
        $section->addText('5б. Нагрузка на площадку и подкосы', DocStyleRegistry::bold(), DocStyleRegistry::paragraphLeft());
        $section->addTextBreak(1);

        $table = $section->addTable(DocStyleRegistry::tableStyle());

        $this->addPlatformHeader($table);

        $arr = $data->toArray();
        foreach ($arr['platformSections'] as $row) {
            $this->addPlatformRow($table, $row);
        }
    }

    private function addPlatformHeader(Table $table): void
    {
        $bold   = DocStyleRegistry::boldCenter();
        $center = DocStyleRegistry::paragraphCenter();
        $hCell  = DocStyleRegistry::headerCell();
        $w      = self::PLATFORM_WIDTHS;

        $table->addRow(600);
        $table->addCell($w[0], $hCell)->addText('Элемент', $bold, $center);
        $table->addCell($w[1], $hCell)->addText('Верхняя отметка, м', $bold, $center);
        $table->addCell($w[2], $hCell)->addText('Высота, м', $bold, $center);
        $table->addCell($w[3], $hCell)->addText('Суммарная нагрузка, кг', $bold, $center);
        $table->addCell($w[4], $hCell)->addText('Нагрузка, кг/(м·пояс)', $bold, $center);
    }

    private function addPlatformRow(Table $table, array $row): void
    {
        $c      = DocStyleRegistry::center();
        $center = DocStyleRegistry::paragraphCenter();
        $dc     = DocStyleRegistry::dataCell();
        $w      = self::PLATFORM_WIDTHS;

        $table->addRow(400);
        $table->addCell($w[0], $dc)->addText($row['label'], $c, $center);
        $table->addCell($w[1], $dc)->addText($this->fmt($row['topHeight'] / 1000), $c, $center);
        $table->addCell($w[2], $dc)->addText($this->fmt($row['height'] / 1000), $c, $center);
        $table->addCell($w[3], $dc)->addText($this->fmt($row['totalLoad'], 1), $c, $center);
        $table->addCell($w[4], $dc)->addText($this->fmt($row['loadPerLinearMeterPerBelt'], 1), $c, $center);
    }

    // ─── Таблица 5в: оборудование по высотным группам ────────────────────────

    private function buildEquipmentTable(Section $section, TotalLoadResponseDto $data): void
    {
        $section->addText('5в. Нагрузка на оборудование по высотным группам', DocStyleRegistry::bold(), DocStyleRegistry::paragraphLeft());
        $section->addTextBreak(1);

        $table = $section->addTable(DocStyleRegistry::tableStyle());

        $this->addEquipmentHeader($table);

        $arr = $data->toArray();
        foreach ($arr['equipmentHeights'] as $row) {
            $this->addEquipmentRow($table, $row);
        }
    }

    private function addEquipmentHeader(Table $table): void
    {
        $bold   = DocStyleRegistry::boldCenter();
        $center = DocStyleRegistry::paragraphCenter();
        $hCell  = DocStyleRegistry::headerCell();
        $w      = self::EQUIPMENT_WIDTHS;

        $table->addRow(600);
        $table->addCell($w[0], $hCell)->addText('№ группы', $bold, $center);
        $table->addCell($w[1], $hCell)->addText('Высотная отметка, мм', $bold, $center);
        $table->addCell($w[2], $hCell)->addText('Суммарная нагрузка, кг', $bold, $center);
    }

    private function addEquipmentRow(Table $table, array $row): void
    {
        $c      = DocStyleRegistry::center();
        $center = DocStyleRegistry::paragraphCenter();
        $dc     = DocStyleRegistry::dataCell();
        $w      = self::EQUIPMENT_WIDTHS;

        $table->addRow(400);
        $table->addCell($w[0], $dc)->addText((string)$row['heightMark'], $c, $center);
        $table->addCell($w[1], $dc)->addText($this->fmt($row['height'], 0), $c, $center);
        $table->addCell($w[2], $dc)->addText($this->fmt($row['totalLoad'], 1), $c, $center);
    }

    private function fmt(float $value, int $decimals = 2): string
    {
        return number_format($value, $decimals, ',', '');
    }
}
