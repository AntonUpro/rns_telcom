<?php

declare(strict_types=1);

namespace App\Service\DocumentGenerator\Report\Section;

use App\Service\DocumentGenerator\DocStyleRegistry;
use App\Service\DocumentGenerator\Report\ReportContext;
use App\Service\DocumentGenerator\Report\SectionBuilderInterface;
use PhpOffice\PhpWord\Element\Section;

/**
 * Раздел «Заключение».
 * Вывод о соответствии/несоответствии требованиям НД на основании результатов расчёта.
 */
final class ConclusionSection implements SectionBuilderInterface
{
    public function build(Section $section, ReportContext $context): void
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
    }
}
