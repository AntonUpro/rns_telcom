<?php

declare(strict_types=1);

namespace App\Service\DocumentGenerator\Table;

use App\Dto\Calculation\Equipment\Calculate\EquipmentCalculationResult;
use App\Enum\Equipment\EquipmentGroupEnum;
use App\Enum\Equipment\EquipmentTypeEnum;
use App\Service\DocumentGenerator\DocStyleRegistry;
use PhpOffice\PhpWord\Element\Section;
use PhpOffice\PhpWord\Element\Table;

/**
 * Строит таблицу ветрового давления на навесное оборудование (Таблица 2).
 *
 * Структура входных данных:
 *   array<string, array<string, EquipmentCalculationResult[]>>
 *   [groupValue][typeValue][] = EquipmentCalculationResult
 *
 * Столбцы (14 шт.):
 *   Обозначение оборудования | N, шт | Нподвеса, м | k(ze) | Aоб, м²
 *   | Ат/с, м² | W₀, кг/м² | γ | Сх∞ | Kλ | Сх(об) | Сх(т/с) | µ | P, кг/ед.об.
 */
final class EquipmentWindPressureTableBuilder
{
    // суммарно 10000
    private const COL_WIDTHS = [
        2200,  // Обозначение оборудования, габариты
        500,   // N, шт
        700,   // Нподвеса, м
        600,   // k(ze)
        600,   // Aоб, м²
        600,   // Ат/с, м²
        700,   // W₀, кг/м²
        500,   // γ
        600,   // Сх∞
        600,   // Kλ
        600,   // Сх(об)
        600,   // Сх(т/с)
        500,   // µ
        700,   // P, кг/ед.об.
    ];

    private const TOTAL_COLS = 14;

    /**
     * @param array<string, array<string, EquipmentCalculationResult[]>> $data
     */
    public function build(Section $section, array $data): void
    {
        $section->addText('Ветровое давление на навесное оборудование', DocStyleRegistry::sectionTitle(), DocStyleRegistry::paragraphTitle());
        $section->addTextBreak(1);

        $section->addText(
            'Состав оборудования принят в соответствии с предоставленной документацией и результатами натурного обследования:',
            DocStyleRegistry::normalText(),
            DocStyleRegistry::paragraphLeft(),
        );
        $section->addTextBreak(1);

        $section->addText(
            'Таблица 2',
            DocStyleRegistry::normalText(),
            DocStyleRegistry::paragraphRight(),
        );

        $table = $section->addTable(DocStyleRegistry::tableStyle());

        $this->addHeader($table);
        $this->fillData($table, $data);

        $section->addTextBreak(1);
        $section->addText(
            'Ветровое давление P [кг] приведено на единицу навесного антенного оборудования.',
            DocStyleRegistry::normalText(),
            DocStyleRegistry::paragraphLeft(),
        );
    }

    private function addHeader(Table $table): void
    {
        $bold   = DocStyleRegistry::italicCenter();
        $center = DocStyleRegistry::paragraphCenter();
        $hCell  = DocStyleRegistry::headerCell();
        $w      = self::COL_WIDTHS;

        // ── Строка: заголовки столбцов ───────────────────────────────────────
        $table->addRow(600);

        $headers = [
            'Обозначение оборудования, габариты',
            'N, шт',
            'Нподвеса, м',
            'k(ze)',
            'Aоб, м²',
            'Ат/с, м²',
            'W₀, кг/м²',
            'γ',
            'Сх∞',
            'Kλ',
            'Сх(об)',
            'Сх(т/с)',
            'µ',
            'P, кг/ед.об.',
        ];

        foreach ($headers as $i => $header) {
            $table->addCell($w[$i], $hCell)->addText($header, $bold, $center);
        }
    }

    /**
     * @param array<string, array<string, EquipmentCalculationResult[]>> $data
     */
    private function fillData(Table $table, array $data): void
    {
        $bold       = DocStyleRegistry::italicCenter();
        $normal     = DocStyleRegistry::center();
        $center     = DocStyleRegistry::paragraphCenter();
        $catRow     = DocStyleRegistry::categoryRow();
        $subRow     = DocStyleRegistry::subcategoryRow();
        $dataCell   = DocStyleRegistry::dataCell();
        $total      = array_sum(self::COL_WIDTHS);

        foreach ($data as $groupValue => $byType) {
            $groupLabel = EquipmentGroupEnum::from($groupValue)->label();

            // ── Строка группы (Существующее / Планируемое / Демонтируемое) ──
            $table->addRow(400);
            $table->addCell($total, array_merge($catRow, ['gridSpan' => self::TOTAL_COLS]))
                ->addText($groupLabel, $bold, $center);

            foreach ($byType as $typeValue => $results) {
                $typeLabel = EquipmentTypeEnum::from($typeValue)->label();

                // ── Строка подгруппы (РРЛ / Панельная / Радиоблок) ──────────
                $table->addRow(400);
                $table->addCell($total, array_merge($subRow, ['gridSpan' => self::TOTAL_COLS]))
                    ->addText($typeLabel, $bold, $center);

                // ── Строки оборудования ──────────────────────────────────────
                foreach ($results as $r) {
                    $this->addResultRow($table, $r, $dataCell, $normal, $center);
                }
            }
        }
    }

    private function addResultRow(
        Table $table,
        EquipmentCalculationResult $r,
        array $cellStyle,
        array $fontStyle,
        array $paraStyle,
    ): void {
        $w = self::COL_WIDTHS;

        $table->addRow(400);

        $table->addCell($w[0],  $cellStyle)->addText($r->fullName, $fontStyle, DocStyleRegistry::paragraphLeft());
        $table->addCell($w[1],  $cellStyle)->addText((string)$r->quantity, $fontStyle, $paraStyle);
        $table->addCell($w[2],  $cellStyle)->addText($this->fmt($r->monthHeight), $fontStyle, $paraStyle);
        $table->addCell($w[3],  $cellStyle)->addText($this->fmt($r->kze), $fontStyle, $paraStyle);
        $table->addCell($w[4],  $cellStyle)->addText($this->fmt($r->oneEquipmentArea), $fontStyle, $paraStyle);
        $table->addCell($w[5],  $cellStyle)->addText($this->fmt($r->pipeStandArea), $fontStyle, $paraStyle);
        $table->addCell($w[6],  $cellStyle)->addText($this->fmt($r->windPress), $fontStyle, $paraStyle);
        $table->addCell($w[7],  $cellStyle)->addText($this->fmt($r->shadingCoefficient, 1), $fontStyle, $paraStyle);
        $table->addCell($w[8],  $cellStyle)->addText($this->fmt($r->cxInf), $fontStyle, $paraStyle);
        $table->addCell($w[9],  $cellStyle)->addText($this->fmt($r->kLambda), $fontStyle, $paraStyle);
        $table->addCell($w[10], $cellStyle)->addText($this->fmt($r->cxEquipment), $fontStyle, $paraStyle);
        $table->addCell($w[11], $cellStyle)->addText($this->fmt($r->cxPipeStand, 1), $fontStyle, $paraStyle);
        $table->addCell($w[12], $cellStyle)->addText($this->fmt($r->securityCoefficient, 1), $fontStyle, $paraStyle);
        $table->addCell($w[13], $cellStyle)->addText($this->fmt($r->pressOnOneEquipment, 1), $fontStyle, $paraStyle);
    }

    private function fmt(float $value, int $decimals = 2): string
    {
        return number_format($value, $decimals, ',', '');
    }
}
