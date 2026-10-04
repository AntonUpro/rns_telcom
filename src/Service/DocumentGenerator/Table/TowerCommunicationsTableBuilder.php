<?php

declare(strict_types=1);

namespace App\Service\DocumentGenerator\Table;

use App\Dto\Calculation\Pillar\PartSectionDto;
use App\Dto\Calculation\Tower\TowerCommunicationsSectionDto;
use App\Service\DocumentGenerator\DocStyleRegistry;
use PhpOffice\PhpWord\Element\Section;
use PhpOffice\PhpWord\Element\Table;

/**
 * Строит таблицу ветрового давления на коммуникации башни.
 *
 * Столбцы (14 шт.):
 *   № | z, м | k(ze) | W₀, кг/м² | γ
 *   | [Кабельная трасса: A, Сх, P]
 *   | [Кабельные полки, кабельрост: A, Сх, P]
 *   | [Лестница: A, Сх, P]
 */
final class TowerCommunicationsTableBuilder
{
    private const COL_WIDTHS = [
        400,   // №
        700,   // z, м
        600,   // k(ze)
        700,   // W₀, кг/м²
        500,   // γ
        // Кабельная трасса
        700, 600, 700,
        // Кабельные полки, кабельрост
        700, 600, 700,
        // Лестница
        700, 600, 700,
    ];

    private const BASE_HEADERS = ['№', 'z, м', 'k(ze)', 'W₀, кг/м²', 'γ'];

    private const GROUP_HEADERS = [
        'Кабельная трасса',
        'Кабельные полки, кабельрост',
        'Лестница',
    ];

    /**
     * @param TowerCommunicationsSectionDto[] $sections
     */
    public function build(Section $section, array $sections, int &$tableNum): void
    {
        $section->addText(
            'Кабельная трасса, кабельные полки и кабельрост, лестница:',
            DocStyleRegistry::normalText(),
            DocStyleRegistry::paragraphIndentWithKeepNext(),
        );

        $tableNum++;
        $section->addText('Таблица ' . $tableNum, DocStyleRegistry::normalText(), DocStyleRegistry::paragraphRight());

        $table = $section->addTable(DocStyleRegistry::tableStyle());
        $this->addHeader($table);

        foreach ($sections as $dto) {
            $this->addDataRow($table, $dto);
        }
    }

    private function addHeader(Table $table): void
    {
        $italic = DocStyleRegistry::italicCenter();
        $center = array_merge(DocStyleRegistry::paragraphCenter(), ['keepNext' => true]);
        $hCell = DocStyleRegistry::headerCell();
        $w = self::COL_WIDTHS;
        $baseCount = count(self::BASE_HEADERS);

        // ── Строка 1: базовые столбцы + названия групп ──────────────────────
        $table->addRow(600, ['cantSplit' => true]);
        foreach (self::BASE_HEADERS as $i => $header) {
            $table->addCell($w[$i], [...$hCell, 'vMerge' => 'restart'])->addText($header, $italic, $center);
        }
        foreach (self::GROUP_HEADERS as $g => $name) {
            $offset = $baseCount + $g * 3;
            $table->addCell($w[$offset] + $w[$offset + 1] + $w[$offset + 2], [...$hCell, 'gridSpan' => 3])
                ->addText($name, $italic, $center);
        }

        // ── Строка 2: подзаголовки групп ─────────────────────────────────────
        $table->addRow(500, ['cantSplit' => true]);
        for ($i = 0; $i < $baseCount; $i++) {
            $table->addCell($w[$i], ['vMerge' => 'continue']);
        }
        foreach (array_keys(self::GROUP_HEADERS) as $g) {
            $offset = $baseCount + $g * 3;
            $table->addCell($w[$offset], $hCell)->addText('A, м²', $italic, $center);
            $table->addCell($w[$offset + 1], $hCell)->addText('Сх', $italic, $center);
            $table->addCell($w[$offset + 2], $hCell)->addText('P, кг', $italic, $center);
        }
    }

    private function addDataRow(Table $table, TowerCommunicationsSectionDto $dto): void
    {
        $center = array_merge(DocStyleRegistry::paragraphCenter(), ['keepNext' => true]);
        $c = DocStyleRegistry::center();
        $dc = DocStyleRegistry::dataCell();
        $w = self::COL_WIDTHS;

        $table->addRow(400, ['cantSplit' => true]);

        $values = [
            (string)$dto->sectionNumber,
            $this->fmt($dto->topMark / 1000, 3),
            $this->fmt($dto->kze),
            $this->fmt($dto->windPress, 1),
            $this->fmt($dto->securityCoefficient, 1),
        ];
        foreach ($values as $i => $value) {
            $table->addCell($w[$i], $dc)->addText($value, $c, $center);
        }

        foreach ([$dto->cable, $dto->cableChannel, $dto->ladder] as $g => $part) {
            $this->addPartCells($table, $part, array_slice($w, count($values) + $g * 3, 3), $c, $center, $dc);
        }
    }

    private function addPartCells(
        Table $table,
        ?PartSectionDto $part,
        array $widths,
        array $fontStyle,
        array $paraStyle,
        array $cellStyle,
    ): void {
        $values = $part === null
            ? ['—', '—', '—']
            : [$this->fmt($part->area), $this->fmt($part->cx), $this->fmt($part->press, 1)];

        foreach ($values as $i => $value) {
            $table->addCell($widths[$i], $cellStyle)->addText($value, $fontStyle, $paraStyle);
        }
    }

    private function fmt(float $value, int $decimals = 2): string
    {
        return number_format($value, $decimals, ',', '');
    }
}
