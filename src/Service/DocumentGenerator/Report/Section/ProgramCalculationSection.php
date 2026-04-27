<?php

declare(strict_types=1);

namespace App\Service\DocumentGenerator\Report\Section;

use App\Entity\CalculationImage;
use App\Entity\CalculationReportFile;
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
    public function build(Section $section, ReportContext $context): void
    {
        $body  = DocStyleRegistry::bodyText();
        $left  = DocStyleRegistry::paragraphLeft();
        $center = ['alignment' => Jc::CENTER];

        $section->addText(
            'Расчётная схема опоры',
            $body,
            $center,
        );
        $section->addTextBreak(1);

        $imageSchemePC = $context->getCalculationImageByType(CalculationImage::TYPE_SCHEME_PC);
        $imageMosaicSections = $context->getCalculationImageByType(CalculationImage::TYPE_SECTIONS);

        if (file_exists($imageSchemePC->getFilePath()) && file_exists($imageMosaicSections->getFilePath())) {
            $section->addImage($imageSchemePC->getFilePath(), [
                'width'         => Converter::cmToPoint(8),
                'height'        => Converter::cmToPoint(22),
                'wrappingStyle' => 'inline',
                'alignment'     => Jc::CENTER,
            ]);
            $section->addImage($imageMosaicSections->getFilePath(), [
                'width'         => Converter::cmToPoint(5),
                'height'        => Converter::cmToPoint(8),
                'wrappingStyle' => 'inline',
                'alignment'     => Jc::CENTER,
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
                'width'         => Converter::cmToPoint(8),
                'height'        => Converter::cmToPoint(24),
                'wrappingStyle' => 'inline',
                'alignment'     => Jc::CENTER,
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
                'width'         => Converter::cmToPoint(8),
                'height'        => Converter::cmToPoint(24),
                'wrappingStyle' => 'inline',
                'alignment'     => Jc::CENTER,
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
                'width'         => Converter::cmToPoint(8),
                'height'        => Converter::cmToPoint(24),
                'wrappingStyle' => 'inline',
                'alignment'     => Jc::CENTER,
            ]);
        }
    }
}
