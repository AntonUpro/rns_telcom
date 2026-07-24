<?php

declare(strict_types=1);

namespace App\Service\DocumentGenerator\Report\Section;

use App\Service\DocumentGenerator\DocStyleRegistry;
use App\Service\DocumentGenerator\Report\ReportContext;
use App\Service\DocumentGenerator\Report\SectionBuilderInterface;
use PhpOffice\PhpWord\ComplexType\TblWidth as TblWidthType;
use PhpOffice\PhpWord\Element\Section;
use PhpOffice\PhpWord\Shared\Converter;
use PhpOffice\PhpWord\SimpleType\Jc;
use PhpOffice\PhpWord\SimpleType\TblWidth;

/**
 * Раздел «Заключение».
 * Вывод о соответствии/несоответствии требованиям НД на основании результатов расчёта.
 */
final class ConclusionSection implements SectionBuilderInterface
{
    public function build(Section $section, ReportContext $context, int &$tableNum): void
    {
        $height = $context->getHeightM();
        $address = $context->getAddress();
        $body = DocStyleRegistry::bodyText();
        $para = DocStyleRegistry::paragraphIndent();

        $constructKuse = $context->getMaxK();
        $structureFails = $constructKuse !== null && $constructKuse > 1.0;
        $deformationFails = ($k = $context->getDeformationMaxKuse()) !== null && $k > 1.0;

        $structureVerdict = $structureFails ? 'не соответствует' : 'соответствует';
        $deformationVerdict = $deformationFails ? 'не соответствует' : 'соответствует';

        $textRun = $section->addTextRun($para);
        $textRun->addText(
            sprintf(
                'В результате проведения поверки расчёта конструкций опоры Н=%s м, '
                . 'расположенной по адресу: %s, показал, что несущая способность конструкций опоры при воздействии расчётных нагрузок ',
                $height,
                $address,
            ),
            $body,
        );
        $textRun->addText(
            $structureVerdict, $structureFails
            ? DocStyleRegistry::titleTableTextUnderlineBold()
            : DocStyleRegistry::titleTableTextUnderline()
        );
        $textRun->addText(' требованиям нормативной документации. Деформации конструкций ствола опоры при воздействии нормативных нагрузок ', $body);
        $textRun->addText(
            $deformationVerdict, $deformationFails
            ? DocStyleRegistry::titleTableTextUnderlineBold()
            : DocStyleRegistry::titleTableTextUnderline()
        );
        $textRun->addText(' требованиям нормативной документации.', $body);

        $section->addTextBreak(1);


        if ($structureFails || $deformationFails) {
            $resultTableTypes = $context->getNegativeCalculations();

            $textRunModern = $section->addTextRun($para);
            $textRunModern->addText('Модернизация антенно-фидерного оборудования ', $body);
            $textRunModern->addText('не допускается', DocStyleRegistry::titleTableTextUnderlineBold());
            $textRunModern->addText(' без проведения компенсирующих мероприятий.', $body);
            $textRunModern->addText(' Метод и объем усиления определить проектом на усиление, разработанным специализированной организацией, имеющей соответствующую Лицензию:', $body);
            foreach ($resultTableTypes as $type) {
                $section->addText('– ' . $type->constructFormulation() . ';', $body, $para);
            }
        } else {
            $section->addText(
                'Модернизация антенно-фидерного оборудования допускается без проведения компенсирующих мероприятий.',
                $body,
                $para,
            );
        }

        $section->addTextBreak(2);

        $this->buildSignatureBlock($section, $context);
    }

    private function buildSignatureBlock(Section $section, ReportContext $context): void
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
