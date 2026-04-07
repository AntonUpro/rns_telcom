<?php

declare(strict_types=1);

namespace App\Service\DocumentGenerator\Table;

use App\Dto\Calculation\Pillar\PillarCalculationDto;
use App\Dto\Calculation\Pillar\PartSectionDto;
use App\Dto\Calculation\Pillar\SectionCollectionDto;
use App\Service\DocumentGenerator\DocStyleRegistry;
use PhpOffice\PhpWord\Element\Section;
use PhpOffice\PhpWord\Element\Table;

/**
 * Строит таблицу ветрового давления на ствол опоры и коммуникации.
 *
 * Столбцы (18 шт.):
 *   № | z верх, м | h, м | k(ze) | W₀, кг/м² | γ
 *   | [Ствол: A, Сх, P]
 *   | [Кабельные полки: A, Сх, P]
 *   | [Кабели: A, Сх, P]
 *   | [Лестница: A, Сх, P]
 */
final class PillarSectionsTableBuilder
{
    private const COL_WIDTHS = [
        400,   // №
        600,   // z верх, м
        600,   // h, м
        500,   // k(ze)
        600,   // W₀, кг/м²
        500,   // γ
        // Ствол
        600,   // A, м²
        500,   // Сх
        600,   // P, кг
        // Кабельные полки
        600,   // A, м²
        500,   // Сх
        600,   // P, кг
        // Кабели
        600,   // A, м²
        500,   // Сх
        600,   // P, кг
        // Лестница
        600,   // A, м²
        500,   // Сх
        600,   // P, кг
    ];

    private const GROUP_HEADERS = [
        'Ствол опоры',
        'Кабельные полки, кабельрост',
        'Кабельная трасса',
        'Лестница',
    ];

    public function build(Section $section, SectionCollectionDto $data): void
    {
        $section->addText('Ветровое давление на ствол опоры и коммуникации', DocStyleRegistry::sectionTitle(), DocStyleRegistry::paragraphTitle());
        $section->addTextBreak(1);

        $table = $section->addTable(DocStyleRegistry::tableStyle());

        $this->addHeader($table);

        foreach ($data->getSections() as $dto) {
            $this->addDataRow($table, $dto);
        }
    }

    private function addHeader(Table $table): void
    {
        $italic   = DocStyleRegistry::italicCenter();
        $center = DocStyleRegistry::paragraphCenter();
        $hCell  = DocStyleRegistry::headerCell();
        $w      = self::COL_WIDTHS;

        // ── Строка 1: базовые столбцы + названия групп ──────────────────────
        $table->addRow(600);

        $table->addCell($w[0], [...$hCell, 'vMerge' => 'restart'])->addText('№', $italic, $center);
        $table->addCell($w[1], [...$hCell, 'vMerge' => 'restart'])->addText('z верх, м', $italic, $center);
        $table->addCell($w[2], [...$hCell, 'vMerge' => 'restart'])->addText('h, м', $italic, $center);
        $table->addCell($w[3], [...$hCell, 'vMerge' => 'restart'])->addText('k(ze)', $italic, $center);
        $table->addCell($w[4], [...$hCell, 'vMerge' => 'restart'])->addText('W₀, кг/м²', $italic, $center);
        $table->addCell($w[5], [...$hCell, 'vMerge' => 'restart'])->addText('γ', $italic, $center);

        foreach (self::GROUP_HEADERS as $i => $name) {
            $width = $w[6 + $i * 3] + $w[7 + $i * 3] + $w[8 + $i * 3];
            $table->addCell($width, array_merge($hCell, ['gridSpan' => 3]))->addText($name, $italic, $center);
        }

        // ── Строка 2: подзаголовки подгрупп ─────────────────────────────────
        $table->addRow(500);

        $table->addCell($w[0], ['vMerge' => 'continue']);
        $table->addCell($w[1], ['vMerge' => 'continue']);
        $table->addCell($w[2], ['vMerge' => 'continue']);
        $table->addCell($w[3], ['vMerge' => 'continue']);
        $table->addCell($w[4], ['vMerge' => 'continue']);
        $table->addCell($w[5], ['vMerge' => 'continue']);

        for ($g = 0; $g < 4; $g++) {
            $table->addCell($w[6 + $g * 3], $hCell)->addText('A, м²', $italic, $center);
            $table->addCell($w[7 + $g * 3], $hCell)->addText('Сх', $italic, $center);
            $table->addCell($w[8 + $g * 3], $hCell)->addText('P, кг', $italic, $center);
        }
    }

    private function addDataRow(Table $table, PillarCalculationDto $dto): void
    {
        $meta   = $dto->totalCalculationDataDto;
        $center = DocStyleRegistry::paragraphCenter();
        $c      = DocStyleRegistry::center();
        $dc     = DocStyleRegistry::dataCell();
        $w      = self::COL_WIDTHS;

        $table->addRow(400);

        $table->addCell($w[0], $dc)->addText((string)$meta->numberSection, $c, $center);
        $table->addCell($w[1], $dc)->addText($this->fmt($meta->topHeight / 1000), $c, $center);
        $table->addCell($w[2], $dc)->addText($this->fmt($meta->heightSection / 1000), $c, $center);
        $table->addCell($w[3], $dc)->addText($this->fmt($meta->kze), $c, $center);
        $table->addCell($w[4], $dc)->addText($this->fmt($meta->windPress), $c, $center);
        $table->addCell($w[5], $dc)->addText($this->fmt($meta->shadingCoefficient), $c, $center);

        $this->addPartCells($table, $dto->pillarPart, array_slice($w, 6, 3), $c, $center, $dc);
        $this->addPartCells($table, $dto->cableChanelPart, array_slice($w, 9, 3), $c, $center, $dc);
        $this->addPartCells($table, $dto->cablePart, array_slice($w, 12, 3), $c, $center, $dc);
        $this->addPartCells($table, $dto->ladderPart, array_slice($w, 15, 3), $c, $center, $dc);
    }

    private function addPartCells(
        Table $table,
        ?PartSectionDto $part,
        array $widths,
        array $fontStyle,
        array $paraStyle,
        array $cellStyle,
    ): void {
        if ($part === null) {
            $table->addCell($widths[0], $cellStyle)->addText('—', $fontStyle, $paraStyle);
            $table->addCell($widths[1], $cellStyle)->addText('—', $fontStyle, $paraStyle);
            $table->addCell($widths[2], $cellStyle)->addText('—', $fontStyle, $paraStyle);
            return;
        }

        $table->addCell($widths[0], $cellStyle)->addText($this->fmt($part->area), $fontStyle, $paraStyle);
        $table->addCell($widths[1], $cellStyle)->addText($this->fmt($part->cx), $fontStyle, $paraStyle);
        $table->addCell($widths[2], $cellStyle)->addText($this->fmt($part->press), $fontStyle, $paraStyle);
    }

    private function fmt(float $value, int $decimals = 2): string
    {
        return number_format($value, $decimals, ',', '');
    }
}
