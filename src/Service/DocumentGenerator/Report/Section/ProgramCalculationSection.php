<?php

declare(strict_types=1);

namespace App\Service\DocumentGenerator\Report\Section;

use App\Entity\CalculationImage;
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

        if (file_exists($imageSchemePC->getFilePath()) && file_exists($imageMosaicSections->getFilePath())) {
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
        if (file_exists($imageMosaicN->getFilePath())) {
            $section->addImage($imageMosaicN->getFilePath(), [
                'width' => Converter::cmToPoint(8),
                'height' => Converter::cmToPoint(24),
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
        if (file_exists($imageMosaicM->getFilePath())) {
            $section->addImage($imageMosaicM->getFilePath(), [
                'width' => Converter::cmToPoint(8),
                'height' => Converter::cmToPoint(24),
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
        if (file_exists($imageMosaicDis->getFilePath())) {
            $section->addImage($imageMosaicDis->getFilePath(), [
                'width' => Converter::cmToPoint(8),
                'height' => Converter::cmToPoint(24),
                'wrappingStyle' => 'inline',
                'alignment' => Jc::CENTER,
            ]);
        }

        $section->addPageBreak(1);

        $section->addTitle(sprintf(
            '%d.1 %s %s',
            $this->sectionNum,
            'РАСЧЕТ ЖЕЛЕЗОБЕТОННОЙ СТОЙКИ',
            $context->calculation->getCalculationData()?->getConcretePillarSpecificData()?->pillarStamp,
        ), 1);

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
            $left,
        );
    }
}
