<?php

declare(strict_types=1);

namespace App\Service\DocumentGenerator\Report\Section;

use App\Dto\Calculation\PillarByHeight\SimpleResultDto;
use App\Entity\CalculationImage;
use App\Service\Calculation\PillarByHeight\SimpleCalculator;
use App\Service\DocumentGenerator\DocStyleRegistry;
use App\Service\DocumentGenerator\Report\ReportContext;
use App\Service\DocumentGenerator\Report\SectionBuilderInterface;
use PhpOffice\PhpWord\Element\Section;
use PhpOffice\PhpWord\Shared\Converter;
use PhpOffice\PhpWord\SimpleType\Jc;

/**
 * Раздел «Программный расчёт опоры» — результирующие изображения из TYPE_EQUIPMENT файлов.
 */
final class ProgramCalculationSection implements SectionBuilderInterface
{
    public function __construct(
        private readonly int $sectionNum
    ) {
    }

    public function build(Section $section, ReportContext $context, int &$tableNum): void
    {
        $body = DocStyleRegistry::bodyText();
        $left = DocStyleRegistry::paragraphIndent();
        $center = ['alignment' => Jc::CENTER];

        $section->addText(
            'Расчётная схема опоры',
            $body,
            $center,
        );

        $imageSchemePC = $context->getCalculationImageByType(CalculationImage::TYPE_SCHEME_PC);
        $imageMosaicSections = $context->getCalculationImageByType(CalculationImage::TYPE_SECTIONS);

        if (
            $imageSchemePC
            && $imageMosaicSections
            && file_exists($imageSchemePC->getFilePath())
            && file_exists($imageMosaicSections->getFilePath())
        ) {
            $textRun = $section->addTextRun($center);

            $textRun->addImage($imageSchemePC->getFilePath(), [
//                'width'         => Converter::cmToPoint(8),
                'height' => Converter::cmToPoint(22),
                'wrappingStyle' => 'inline',
                'alignment' => Jc::CENTER,
            ]);
            $textRun->addImage($imageMosaicSections->getFilePath(), [
                'width' => Converter::cmToPoint(5),
                'height' => Converter::cmToPoint(8),
                'wrappingStyle' => 'inline',
                'alignment' => Jc::CENTER,
            ]);
        }
        $section->addPageBreak(1);

        $section->addText(
            'Мозаика максимальных усилий N (продольных сил) в стволе опоры',
            $body,
            $center,
        );
        $imageMosaicN = $context->getCalculationImageByType(CalculationImage::TYPE_MOSAIC_N);
        if ($imageMosaicN && file_exists($imageMosaicN->getFilePath())) {
            $section->addImage($imageMosaicN->getFilePath(), [
                'width' => Converter::cmToPoint(8),
                'height' => Converter::cmToPoint(23),
                'wrappingStyle' => 'inline',
                'alignment' => Jc::CENTER,
            ]);
        }
        $section->addPageBreak(1);

        $section->addText(
            'Мозаика максимальных усилий М (моментов) в стволе опоры',
            $body,
            $center,
        );
        $imageMosaicM = $context->getCalculationImageByType(CalculationImage::TYPE_MOSAIC_M);
        if ($imageMosaicM && file_exists($imageMosaicM->getFilePath())) {
            $section->addImage($imageMosaicM->getFilePath(), [
                'width' => Converter::cmToPoint(8),
                'height' => Converter::cmToPoint(23),
                'wrappingStyle' => 'inline',
                'alignment' => Jc::CENTER,
            ]);
        }
        $section->addPageBreak(1);


        $section->addText(
            'Мозаика максимальных отклонений ствола опоры от вертикали',
            $body,
            $center,
        );
        $imageMosaicDis = $context->getCalculationImageByType(CalculationImage::TYPE_MOSAIC_DISPLACEMENT);
        if ($imageMosaicDis && file_exists($imageMosaicDis->getFilePath())) {
            $section->addImage($imageMosaicDis->getFilePath(), [
                'width' => Converter::cmToPoint(8),
                'height' => Converter::cmToPoint(23),
                'wrappingStyle' => 'inline',
                'alignment' => Jc::CENTER,
            ]);
        }

        $section->addPageBreak(1);

        if ($context->calculation->getCalculationData()?->getCustomer()?->getCode() !== 'NBK') {
            $section->addTitle(sprintf(
                '%d.1 %s %s',
                $this->sectionNum,
                'РАСЧЕТ ЖЕЛЕЗОБЕТОННОЙ СТОЙКИ',
                $context->calculation->getCalculationData()?->getConcretePillarSpecificData()?->pillarStamp,
            ), 2);

            $section->addText(
                'Расчет выполнен согласно Пособию по проектированию предварительно напряженных железобетонных конструкций из тяжелых и легких бетонов, п. 3.43.',
                $body,
                $left,
            );

            $section->addText(
                'В расчете предварительно напряженных элементов учитываем потери '
                . 'предварительного напряжения арматуры при механическом способе натяжения на '
                . 'упоры. Потери предварительного напряжения арматуры определены по табл.4 Пособия.',
                $body,
                $left,
            );

            $section->addText(
                'Исходные данные сечений стойки',
                $body,
                DocStyleRegistry::paragraphIndentWithKeepNext(),
            );

            $calculationResult = (new SimpleCalculator())->calculate($context);

            $this->buildTableInitialData($section, $calculationResult);
            $section->addPageBreak();

            $section->addText(
                'Проверка сечений железобетонной стойки',
                $body,
                DocStyleRegistry::paragraphIndentWithKeepNext(),
            );

            $this->buildTableResult($section, $calculationResult);
        }
    }

    /**
     * @param SimpleResultDto[] $calculationResult
     */
    private function buildTableInitialData(Section $section, array $calculationResult): void
    {
        $italic = DocStyleRegistry::italicCenter();
        $italic['size'] = 8;
        $center = DocStyleRegistry::paragraphCenter();
        $hCell = DocStyleRegistry::headerCell();
        $dCell = DocStyleRegistry::dataCell();

        // Ширины колонок (в twip): Отм | n_пред | Asp | n_неп | As | σsp | D | d | N | Rsp | Rs | Rsc | Rb | Eb | As,tot | A
        $w = [700, 450, 650, 450, 650, 700, 550, 550, 850, 650, 550, 650, 650, 850, 700, 650];

        $tbl = $section->addTable(DocStyleRegistry::tableStyleReport());
        $centerKeep = array_merge($center, ['keepNext' => true]);
        $last = count($calculationResult) - 1;

        // ─── Строка 1: групповые заголовки ───────────────────────────────────
        $tbl->addRow(400, ['cantSplit' => true]);
        // «Отметка» — одна ячейка, будет дублироваться в строке 2
        $tbl->addCell($w[0], $hCell)->addText('', $italic, $centerKeep);
        // Группа «Напрягаемая арм»
        $tbl->addCell($w[1] + $w[2], array_merge($hCell, ['gridSpan' => 2]))->addText('Напрягаемая арм', $italic, $centerKeep);
        // Группа «Ненапрягаемая арм»
        $tbl->addCell($w[3] + $w[4], array_merge($hCell, ['gridSpan' => 2]))->addText('Ненапряг арм', $italic, $centerKeep);
        // Остальные одиночные заголовки
        foreach (array_slice($w, 5) as $width) {
            $tbl->addCell($width, $hCell)->addText('', $italic, $centerKeep);
        }

        // ─── Строка 2: подзаголовки ───────────────────────────────────────────
        $headers = [
            'Отметка, м', 'n, шт', 'Asp, см²', 'n, шт', 'As, см²',
            'σsp, МПа', 'D, мм', 'd, мм', 'N, Н',
            'Rsp, МПа', 'Rs, МПа', 'Rsc, МПа', 'Rb, МПа', 'Eb, МПа',
            'As,tot см²', 'А, м²',
        ];
        $tbl->addRow(400, ['cantSplit' => true]);
        $subHeaderPara = $last >= 0 ? $centerKeep : $center;
        foreach ($headers as $i => $header) {
            $tbl->addCell($w[$i], $hCell)->addText($header, $italic, $subHeaderPara);
        }

        // ─── Строки данных ────────────────────────────────────────────────────
        foreach ($calculationResult as $i => $row){

            $tbl->addRow(300, ['cantSplit' => true]);
            $rowPara = $i < $last ? $centerKeep : $center;
            $vals = [
                number_format($row->mark, 1, ',', ''),
                (string)$row->countPrestressingReinforcement,
                number_format($row->Asp, 2, ',', ''),
                (string)$row->countNonPrestressingReinforcement,
                number_format($row->As, 2, ',', ''),
                number_format($row->tensionSp, 0, ',', ''),
                number_format($row->D, 1, ',', ''),
                number_format($row->d, 1, ',', ''),
                number_format($row->N, 2, ',', ''),
                number_format($row->Rsp, 0, ',', ''),
                number_format($row->Rs, 0, ',', ''),
                number_format($row->Rsc, 0, ',', ''),
                number_format($row->Rb, 2, ',', ''),
                number_format($row->Eb, 0, ',', ''),
                number_format($row->AsTot, 2, ',', ''),
                number_format($row->APillar, 3, ',', ''),
            ];

            foreach ($vals as $j => $val) {
                $tbl->addCell($w[$j], $dCell)->addText($val, $italic, $rowPara);
            }
        }

        $section->addTextBreak(1);
    }

    /**
     * @param SimpleResultDto[] $calculationResult
     */
    private function buildTableResult(Section $section, array $calculationResult): void
    {
        $italic = DocStyleRegistry::italicCenter();
        $italic['size'] = 8;
        $italicExceed = array_merge($italic, ['bold' => true, 'underline' => 'single']);
        $center = DocStyleRegistry::paragraphCenter();
        $hCell = DocStyleRegistry::headerCell();
        $dCell = DocStyleRegistry::dataCell();

        $w = [700, 450, 650, 450, 650, 700, 550, 550, 850, 650, 550, 650, 650, 850, 700, 650];
        $kispColumnIndex = count($w) - 1;

        $tbl = $section->addTable(DocStyleRegistry::tableStyleReport());
        $centerKeep = array_merge($center, ['keepNext' => true]);
        $last = count($calculationResult) - 1;

        $lastExceedIdx = self::lastReinforcementIndex($calculationResult);

        $headers = [
            'Отметка, м', 'rm, м', 'rs, rsp м', 'σbp МПа', 'δsp', 'δs',
            'ωp', 'ωs', 'ξcir', 'zs zsp, мм', 'φsp', 'φs',
            'Мдоп, тс*м', 'Мфакт, Нм', 'Мфакт, тм', 'Кисп',
        ];

        $tbl->addRow(400, ['cantSplit' => true]);
        $headerPara = $last >= 0 ? $centerKeep : $center;
        foreach ($headers as $i => $header) {
            $tbl->addCell($w[$i], $hCell)->addText($header, $italic, $headerPara);
        }

        foreach ($calculationResult as $i => $row) {
            $tbl->addRow(300, ['cantSplit' => true]);
            $rowPara = $i < $last ? $centerKeep : $center;
            $vals = [
                number_format($row->mark, 1, ',', ''),
                number_format($row->rm, 3, ',', ''),
                number_format($row->rsRsp, 3, ',', ''),
                number_format($row->qbp, 1, ',', ''),
                number_format($row->deltaSP, 2, ',', ''),
                number_format($row->deltaS, 2, ',', ''),
                number_format($row->Wp, 2, ',', ''),
                number_format($row->Ws, 2, ',', ''),
                number_format($row->Ecir, 3, ',', ''),
                number_format($row->zsZsp, 1, ',', ''),
                number_format($row->fiSp, 3, ',', ''),
                number_format($row->fiS, 3, ',', ''),
                number_format($row->MAdditional, 2, ',', ''),
                number_format($row->MFactH, 0, ',', ''),
                number_format($row->MFactKg, 2, ',', ''),
                number_format($row->k, 2, ',', ''),
            ];
            foreach ($vals as $j => $val) {
                $cellStyle = ($j === $kispColumnIndex && $i <= $lastExceedIdx) ? $italicExceed : $italic;
                $tbl->addCell($w[$j], $dCell)->addText($val, $cellStyle, $rowPara);
            }
        }

        $section->addTextBreak(1);
    }

    /**
     * Самый верхний индекс (считая от основания опоры, index 0 = низ), где
     * Кисп ещё ≥ 1. Все строки от основания и до него включительно нужно
     * подсвечивать жирным и подчёркиванием — даже если между ними встречаются
     * отметки с Кисп < 1 (учёт вернулся выше единицы).
     *
     * @param SimpleResultDto[] $calculationResult
     */
    private static function lastReinforcementIndex(array $calculationResult): int
    {
        $lastExceedIdx = -1;
        foreach ($calculationResult as $i => $row) {
            if ($row->k >= 1) {
                $lastExceedIdx = $i;
            }
        }

        return $lastExceedIdx;
    }
}
