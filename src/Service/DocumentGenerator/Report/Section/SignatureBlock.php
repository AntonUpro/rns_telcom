<?php

declare(strict_types=1);

namespace App\Service\DocumentGenerator\Report\Section;

use App\Service\DocumentGenerator\Report\ReportContext;
use PhpOffice\PhpWord\ComplexType\TblWidth as TblWidthType;
use PhpOffice\PhpWord\Element\Section;
use PhpOffice\PhpWord\Shared\Converter;
use PhpOffice\PhpWord\SimpleType\Jc;
use PhpOffice\PhpWord\SimpleType\TblWidth;

/**
 * Блок подписей в конце заключения: главный инженер проекта и инженер-проектировщик.
 */
final class SignatureBlock
{
    public function build(Section $section, ReportContext $context): void
    {
        $fStyle = [
            'size' => 12,
            'name' => 'Times New Roman',
            'italic' => true,
        ];
        $cellStyle = ['valign' => 'center'];

        $table = $section->addTable(['indent' => new TblWidthType(Converter::cmToTwip(1), TblWidth::TWIP)]);

        // Строка 1: Главный инженер проекта
        $table->addRow(Converter::cmToTwip(2.5), ['cantSplit' => true]);
        $c1 = $table->addCell(Converter::cmToTwip(7), $cellStyle);
        $c1->addText('Главный инженер проекта:', $fStyle, ['alignment' => Jc::LEFT, 'keepNext' => true]);
        $c2 = $table->addCell(Converter::cmToTwip(5), $cellStyle);
        if ($context->chiefEngineerSignaturePath !== null) {
            $c2->addImage($context->chiefEngineerSignaturePath, [
                'width' => Converter::cmToPoint(3),
                'height' => Converter::cmToPoint(1.5),
                'wrappingStyle' => 'inline',
                'alignment' => Jc::CENTER,
            ]);
        }
        $c3 = $table->addCell(Converter::cmToTwip(5.5), $cellStyle);
        $c3->addText('Лобанов Д. А.', $fStyle, ['alignment' => Jc::LEFT, 'keepNext' => true]);

        // Строка 2: Инженер-проектировщик
        $table->addRow(Converter::cmToTwip(2.5), ['cantSplit' => true]);
        $c1 = $table->addCell(Converter::cmToTwip(7), $cellStyle);
        $c1->addText('Инженер-проектировщик:', $fStyle, ['alignment' => Jc::LEFT]);
        $c2 = $table->addCell(Converter::cmToTwip(5), $cellStyle);
        if ($context->engineerSignaturePath !== null) {
            $c2->addImage($context->engineerSignaturePath, [
                'width' => Converter::cmToPoint(3),
                'height' => Converter::cmToPoint(1.5),
                'wrappingStyle' => 'inline',
                'alignment' => Jc::CENTER,
            ]);
        }
        $c3 = $table->addCell(Converter::cmToTwip(5.5), $cellStyle);
        $c3->addText($context->calculation->getUser()->getShortName() ?? '', $fStyle, ['alignment' => Jc::LEFT]);
    }
}
