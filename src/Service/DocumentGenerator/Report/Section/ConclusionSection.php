<?php

declare(strict_types=1);

namespace App\Service\DocumentGenerator\Report\Section;

use App\Service\DocumentGenerator\DocStyleRegistry;
use App\Service\DocumentGenerator\Report\ReportContext;
use App\Service\DocumentGenerator\Report\SectionBuilderInterface;
use PhpOffice\PhpWord\Element\Section;
use PhpOffice\PhpWord\Shared\Converter;
use PhpOffice\PhpWord\SimpleType\Jc;

/**
 * Раздел «Заключение».
 * Вывод о соответствии/несоответствии требованиям НД на основании результатов расчёта.
 */
final class ConclusionSection implements SectionBuilderInterface
{
    public function build(Section $section, ReportContext $context, int &$tableNum): void
    {
        $height  = $context->getHeightM();
        $address = $context->getAddress();
        $body    = DocStyleRegistry::bodyText();
        $para    = DocStyleRegistry::paragraphIndent();
        $left    = DocStyleRegistry::paragraphLeft();

        $pillarKuse      = $context->getPillarForcesMaxKuse();
        $structureFails  = $pillarKuse !== null && $pillarKuse > 1.0;
        $deformationFails = ($k = $context->getDeformationMaxKuse()) !== null && $k > 1.0;

        $structureVerdict  = $structureFails ? 'не соответствует' : 'соответствует';
        $deformationVerdict = $deformationFails ? 'не соответствует' : 'соответствует';

        $section->addText(
            sprintf(
                'Поверочный расчёт конструкций опоры Н=%s м, расположенной по адресу: %s, '
                . 'показал, что несущая способность конструкций опоры при воздействии расчётных '
                . 'нагрузок %s требованиям нормативной документации.',
                $height,
                $address,
                $structureVerdict,
            ),
            $body,
            $para,
        );
        $section->addTextBreak(1);

        $section->addText(
            sprintf(
                'Деформации конструкций ствола опоры при воздействии нормативных нагрузок '
                . '%s требованиям нормативной документации.',
                $deformationVerdict,
            ),
            $body,
            $para,
        );
        $section->addTextBreak(1);

        if ($structureFails || $deformationFails) {
            $section->addText(
                'Для обеспечения нормативных требований необходимо выполнить усиление конструкций опоры. '
                . 'Метод и объём усиления определить проектом, разработанным специализированной организацией, '
                . 'имеющей соответствующее свидетельство СРО.',
                $body,
                $para,
            );
        } else {
            $section->addText(
                'Конструкции опоры соответствуют требованиям нормативной документации. '
                . 'Установка оборудования допускается без усиления конструкций опоры.',
                $body,
                $para,
            );
        }

        $section->addTextBreak(1);

        $this->buildSignatureBlock($section, $context);
    }

    private function buildSignatureBlock(Section $section, ReportContext $context): void
    {
        $fStyle = [
            'size'  => 12,
            'name'  => 'Times New Roman',
            'italic' => true,
        ];
        $cellStyle = ['valign' => 'center'];

        $table = $section->addTable();

        // Строка 1: Главный инженер проекта
        $table->addRow(Converter::cmToTwip(2.5));
        $c1 = $table->addCell(Converter::cmToTwip(7), $cellStyle);
        $c1->addText('Главный инженер проекта:', $fStyle, ['alignment' => Jc::LEFT]);
        $c2 = $table->addCell(Converter::cmToTwip(5), $cellStyle);
        if ($context->chiefEngineerSignaturePath !== null) {
            $c2->addImage($context->chiefEngineerSignaturePath, [
                'width'         => Converter::cmToPoint(4),
                'height'        => Converter::cmToPoint(2),
                'wrappingStyle' => 'inline',
                'alignment'     => Jc::CENTER,
            ]);
        }
        $c3 = $table->addCell(Converter::cmToTwip(5.5), $cellStyle);
        $c3->addText('Лобанов Д. А.', $fStyle, ['alignment' => Jc::LEFT]);

        // Строка 2: Инженер-проектировщик
        $table->addRow(Converter::cmToTwip(2.5));
        $c1 = $table->addCell(Converter::cmToTwip(7), $cellStyle);
        $c1->addText('Инженер-проектировщик:', $fStyle, ['alignment' => Jc::LEFT]);
        $c2 = $table->addCell(Converter::cmToTwip(5), $cellStyle);
        if ($context->engineerSignaturePath !== null) {
            $c2->addImage($context->engineerSignaturePath, [
                'width'         => Converter::cmToPoint(4),
                'height'        => Converter::cmToPoint(2),
                'wrappingStyle' => 'inline',
                'alignment'     => Jc::CENTER,
            ]);
        }
        $c3 = $table->addCell(Converter::cmToTwip(5.5), $cellStyle);
        $c3->addText($context->calculation->getUser()->getShortName() ?? '', $fStyle, ['alignment' => Jc::LEFT]);
    }
}
