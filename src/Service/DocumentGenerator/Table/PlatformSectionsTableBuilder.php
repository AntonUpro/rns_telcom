<?php

declare(strict_types=1);

namespace App\Service\DocumentGenerator\Table;

use App\Dto\Calculation\PillarPlatform\ElementDto;
use App\Dto\Calculation\PillarPlatform\PillarPlatformSectionDto;
use App\Dto\Calculation\PillarPlatform\TotalPillarPlatformCalculationDto;
use App\Service\DocumentGenerator\DocStyleRegistry;
use PhpOffice\PhpWord\Element\Section;
use PhpOffice\PhpWord\Element\Table;

/**
 * Строит детальную таблицу ветрового давления на площадку и подкосы.
 *
 * Столбцы (20 шт.):
 *   №  | z, м | k(ze) | Элемент | Профиль, мм | l, м | n, шт | Аэл-ов, м²
 *   | ∑Аэл-ов, м² | Схi | Bниз, м | Bверх, м | АK, м²
 *   | Сх | φ1 | η | сt | Wо, кг/м² | Y | P, кг
 *
 * Столбцы №, z, k(ze), ∑Аэл-ов, Bниз, Bверх, АK, Сх, φ1, η, сt, Wо, Y, P
 * объединяются по вертикали (vMerge) на количество элементов в секции.
 */
final class PlatformSectionsTableBuilder
{
    // Индексы столбцов, объединяемых по вертикали (секционные данные)
    private const MERGED_COLS = [0, 1, 2, 8, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19];

    private const COL_WIDTHS = [
        300,   // 0  №
        600,   // 1  z, м
        500,   // 2  k(ze)
        800,   // 3  Элемент
        600,   // 4  Профиль, мм
        500,   // 5  l, м
        400,   // 6  n, шт
        600,   // 7  Аэл-ов, м²
        600,   // 8  ∑Аэл-ов, м²
        400,   // 9  Схi
        500,   // 10 Bниз, м
        500,   // 11 Bверх, м
        600,   // 12 АK, м²
        400,   // 13 Сх
        400,   // 14 φ1
        400,   // 15 η
        400,   // 16 сt
        600,   // 17 Wо, кг/м²
        300,   // 18 Y
        600,   // 19 P, кг
    ];

    private const TEXT_STYLE = ['italic' => true, 'size' => 6.5, 'name' => 'Times New Roman'];

    private const HEADERS = [
        '№',
        'z, м',
        'k(ze)',
        'Элемент',
        'Профиль, мм',
        'l, м',
        'n, шт',
        'Аэл-ов, м²',
        '∑Аэл-ов, м²',
        'Схi',
        'Bниз, м',
        'Bверх, м',
        'АK, м²',
        'Сх',
        'φ1',
        'η',
        'сt',
        'Wо, кг/м²',
        'Y',
        'P, кг',
    ];

    public function build(Section $section, TotalPillarPlatformCalculationDto $data): void
    {
        $section->addText('Площадка и подкосы', DocStyleRegistry::sectionTitle(), DocStyleRegistry::paragraphTitle());
        $section->addTextBreak(1);

        $table = $section->addTable(DocStyleRegistry::tableStyle());

        $this->addHeader($table);

        foreach ($data->platformSections as $dto) {
            $this->addSectionRows($table, $dto);
        }
    }

    private function addHeader(Table $table): void
    {
        $bold   = self::TEXT_STYLE;
        $center = DocStyleRegistry::paragraphCenter();
        $hCell  = DocStyleRegistry::headerCell();

        $table->addRow(600);

        foreach (self::HEADERS as $i => $header) {
            $table->addCell(self::COL_WIDTHS[$i], $hCell)->addText($header, $bold, $center);
        }
    }

    private function addSectionRows(Table $table, PillarPlatformSectionDto $dto): void
    {
        $elements   = $dto->elementsCollectionDto->elements;
        $elemCount  = count($elements);

        if ($elemCount === 0) {
            $this->addEmptySectionRow($table, $dto);
            return;
        }

        foreach ($elements as $rowIndex => $element) {
            $isFirst = ($rowIndex === 0);
            $this->addElementRow($table, $dto, $element, $isFirst, $elemCount);
        }
    }

    private function addElementRow(
        Table $table,
        PillarPlatformSectionDto $dto,
        ElementDto $element,
        bool $isFirst,
        int $totalRows,
    ): void {
        $c      = self::TEXT_STYLE;
        $center = DocStyleRegistry::paragraphCenter();
        $dc     = DocStyleRegistry::dataCell();

        $topHeightM = ($dto->mountingHeightSection + $dto->heightSection) / 1000;

        $table->addRow(400);

        // ── Столбец 0: № (vMerge) ────────────────────────────────────────────
        $this->addMergedCell($table, 0, $isFirst, (string)$dto->numberSection, $totalRows, $c, $center);

        // ── Столбец 1: z, м (vMerge) ─────────────────────────────────────────
        $this->addMergedCell($table, 1, $isFirst, $this->fmt($topHeightM), $totalRows, $c, $center);

        // ── Столбец 2: k(ze) (vMerge) ────────────────────────────────────────
        $this->addMergedCell($table, 2, $isFirst, $this->fmt($dto->kze, 2), $totalRows, $c, $center);

        // ── Столбцы 3..7: данные элемента ────────────────────────────────────
        $profile = $element->sectionConstructType->symbol() . ' ' . (int)$element->with;

        $table->addCell(self::COL_WIDTHS[3], $dc)->addText($element->elementType->label(), $c, $center);
        $table->addCell(self::COL_WIDTHS[4], $dc)->addText($profile, $c, $center);
        $table->addCell(self::COL_WIDTHS[5], $dc)->addText($this->fmt($element->length / 1000), $c, $center);
        $table->addCell(self::COL_WIDTHS[6], $dc)->addText((string)$element->count, $c, $center);
        $table->addCell(self::COL_WIDTHS[7], $dc)->addText($this->fmt($element->areaElements()), $c, $center);

        // ── Столбец 8: ∑Аэл-ов (vMerge) ─────────────────────────────────────
        $this->addMergedCell($table, 8, $isFirst, $this->fmt($dto->elementsCollectionDto->sumElementArea()), $totalRows, $c, $center);

        // ── Столбец 9: Схi (данные элемента) ─────────────────────────────────
        $table->addCell(self::COL_WIDTHS[9], $dc)->addText($this->fmt($element->sectionConstructType->cx(), 1), $c, $center);

        // ── Столбцы 10..19: секционные данные (vMerge) ───────────────────────
        $this->addMergedCell($table, 10, $isFirst, $this->fmt($dto->widthBottom / 1000, 2), $totalRows, $c, $center);
        $this->addMergedCell($table, 11, $isFirst, $this->fmt($dto->widthTop / 1000, 2), $totalRows, $c, $center);
        $this->addMergedCell($table, 12, $isFirst, $this->fmt($dto->areaContourSection, 2), $totalRows, $c, $center);
        $this->addMergedCell($table, 13, $isFirst, $this->fmt($dto->cx, 2), $totalRows, $c, $center);
        $this->addMergedCell($table, 14, $isFirst, $this->fmt($dto->fi, 2), $totalRows, $c, $center);
        $this->addMergedCell($table, 15, $isFirst, $this->fmt($dto->nu,2), $totalRows, $c, $center);
        $this->addMergedCell($table, 16, $isFirst, $this->fmt($dto->ct, 2), $totalRows, $c, $center);
        $this->addMergedCell($table, 17, $isFirst, $this->fmt($dto->windPress, 0), $totalRows, $c, $center);
        $this->addMergedCell($table, 18, $isFirst, $this->fmt($dto->shadingCoefficient, 1), $totalRows, $c, $center);
        $this->addMergedCell($table, 19, $isFirst, $this->fmt($dto->press, 1), $totalRows, $c, $center);
    }

    /**
     * Добавляет ячейку с вертикальным объединением (vMerge).
     * В первой строке секции записывает значение и начинает слияние,
     * в остальных — добавляет пустую ячейку-продолжение.
     */
    private function addMergedCell(
        Table $table,
        int $colIndex,
        bool $isFirst,
        string $value,
        int $totalRows,
        array $fontStyle,
        array $paraStyle,
    ): void {
        $width = self::COL_WIDTHS[$colIndex];

        if ($totalRows === 1) {
            $table->addCell($width, DocStyleRegistry::dataCell())->addText($value, $fontStyle, $paraStyle);
            return;
        }

        if ($isFirst) {
            $table->addCell($width, ['valign' => 'center', 'vMerge' => 'restart'])->addText($value, $fontStyle, $paraStyle);
        } else {
            $table->addCell($width, ['vMerge' => 'continue']);
        }
    }

    /**
     * Строка-заглушка для секции без элементов.
     */
    private function addEmptySectionRow(Table $table, PillarPlatformSectionDto $dto): void
    {
        $c      = self::TEXT_STYLE;
        $center = DocStyleRegistry::paragraphCenter();
        $dc     = DocStyleRegistry::dataCell();
        $topHeightM = ($dto->mountingHeightSection + $dto->heightSection) / 1000;

        $table->addRow(400);
        $table->addCell(self::COL_WIDTHS[0], $dc)->addText((string)$dto->numberSection, $c, $center);
        $table->addCell(self::COL_WIDTHS[1], $dc)->addText($this->fmt($topHeightM), $c, $center);
        $table->addCell(self::COL_WIDTHS[2], $dc)->addText($this->fmt($dto->kze, 2), $c, $center);

        for ($i = 3; $i <= 7; $i++) {
            $table->addCell(self::COL_WIDTHS[$i], $dc)->addText('—', $c, $center);
        }

        $table->addCell(self::COL_WIDTHS[8],  $dc)->addText($this->fmt($dto->elementsCollectionDto->sumElementArea(), 2), $c, $center);
        $table->addCell(self::COL_WIDTHS[9],  $dc)->addText('—', $c, $center);
        $table->addCell(self::COL_WIDTHS[10], $dc)->addText($this->fmt($dto->widthBottom / 1000, 2), $c, $center);
        $table->addCell(self::COL_WIDTHS[11], $dc)->addText($this->fmt($dto->widthTop / 1000, 2), $c, $center);
        $table->addCell(self::COL_WIDTHS[12], $dc)->addText($this->fmt($dto->areaContourSection), $c, $center);
        $table->addCell(self::COL_WIDTHS[13], $dc)->addText($this->fmt($dto->cx, 2), $c, $center);
        $table->addCell(self::COL_WIDTHS[14], $dc)->addText($this->fmt($dto->fi, 2), $c, $center);
        $table->addCell(self::COL_WIDTHS[15], $dc)->addText($this->fmt($dto->nu, 2), $c, $center);
        $table->addCell(self::COL_WIDTHS[16], $dc)->addText($this->fmt($dto->ct, 2), $c, $center);
        $table->addCell(self::COL_WIDTHS[17], $dc)->addText($this->fmt($dto->windPress, 0), $c, $center);
        $table->addCell(self::COL_WIDTHS[18], $dc)->addText($this->fmt($dto->shadingCoefficient, 1), $c, $center);
        $table->addCell(self::COL_WIDTHS[19], $dc)->addText($this->fmt($dto->press, 1), $c, $center);
    }

    private function fmt(float $value, int $decimals = 3): string
    {
        return number_format($value, $decimals, ',', '');
    }
}
