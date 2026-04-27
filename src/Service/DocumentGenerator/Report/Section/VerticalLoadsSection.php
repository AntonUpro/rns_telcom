<?php

declare(strict_types=1);

namespace App\Service\DocumentGenerator\Report\Section;

use App\Service\DocumentGenerator\DocStyleRegistry;
use App\Service\DocumentGenerator\Report\ReportContext;
use App\Service\DocumentGenerator\Report\SectionBuilderInterface;
use PhpOffice\PhpWord\Element\Section;

/**
 * Раздел «Вертикальные нагрузки» — стандартный текст.
 */
final class VerticalLoadsSection implements SectionBuilderInterface
{
    public function build(Section $section, ReportContext $context): void
    {
        $body = DocStyleRegistry::bodyText();
        $para = DocStyleRegistry::paragraphIndent();

        $section->addTitle('9.1 СОБСТВЕННЫЙ ВЕС', 2);

        $lines = [
            'Вертикальная нагрузка от собственного веса конструкций опоры прикладывается к расчетной модели автоматически. Масса прочих конструкций (лестниц, площадок, трубостоек, кабельроста и т.д.) определена в соответствии с технической документацией на применяемый сортамент изделия.',
            'Вес кабельной трассы прикладывается равномерно-распределенной нагрузкой на стержни расчетной модели. Массы кабелей определены в соответствии с данными производителя.',
            'Масса оборудования определена в соответствии с технической документацией производителя.',
        ];

        foreach ($lines as $line) {
            $section->addText($line, $body, $para);
        }

        $section->addTextBreak(1);
    }
}
