<?php

declare(strict_types=1);

namespace App\Service\DocumentGenerator\Table;

use App\Dto\Calculation\Tower\TowerIceLoadRowDto;
use App\Service\DocumentGenerator\DocStyleRegistry;
use PhpOffice\PhpWord\Element\Section;

/**
 * Строит таблицу гололёдной нагрузки на секции башни.
 *
 * Столбцы: Секция | Отметка верха, м | k для отм. середины секции | μ2 | b, мм | ρ, г/см³ | g, м/с² | γf | i', Па
 */
final class TowerIceLoadTableBuilder
{
    private const COL_WIDTHS = [900, 1300, 1500, 800, 900, 1000, 1000, 800, 1100];

    private const HEADERS = [
        'Секция',
        'Отметка верха, м',
        'k для отм. середины секции',
        'μ2',
        'b, мм',
        'ρ, г/см³',
        'g, м/с²',
        'γf',
        "i', Па",
    ];

    /**
     * @param TowerIceLoadRowDto[] $rows
     */
    public function build(Section $section, array $rows, int &$tableNum): void
    {
        $tableNum++;
        $section->addText('Таблица ' . $tableNum, DocStyleRegistry::normalText(), DocStyleRegistry::paragraphRight());

        $table = $section->addTable(DocStyleRegistry::tableStyle());
        $italic = DocStyleRegistry::italicCenter();
        $c = DocStyleRegistry::center();
        $center = array_merge(DocStyleRegistry::paragraphCenter(), ['keepNext' => true]);
        $hCell = DocStyleRegistry::headerCell();
        $dc = DocStyleRegistry::dataCell();

        $table->addRow(600, ['cantSplit' => true]);
        foreach (self::HEADERS as $i => $header) {
            $table->addCell(self::COL_WIDTHS[$i], $hCell)->addText($header, $italic, $center);
        }

        foreach ($rows as $row) {
            $values = [
                (string)$row->sectionNumber,
                $this->fmt($row->topMark, 3),
                $this->fmt($row->k),
                $this->fmt($row->mu2, 1),
                $this->fmt($row->thickness, 0),
                $this->fmt($row->density, 1),
                $this->fmt($row->gravity),
                $this->fmt($row->reliabilityCoefficient, 1),
                $this->fmt($row->load),
            ];

            $table->addRow(400, ['cantSplit' => true]);
            foreach ($values as $i => $value) {
                $table->addCell(self::COL_WIDTHS[$i], $dc)->addText($value, $c, $center);
            }
        }
    }

    private function fmt(float $value, int $decimals = 2): string
    {
        return number_format($value, $decimals, ',', '');
    }
}
