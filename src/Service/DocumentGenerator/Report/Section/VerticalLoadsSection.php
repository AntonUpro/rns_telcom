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
        $para = DocStyleRegistry::paragraphLeft();

        $section->addTitle('9.1 СОБСТВЕННЫЙ ВЕС', 2);

        $lines = [
            '— коэффициент надёжности для собственного веса конструкций и оборудования 1,05;',
            '— коэффициент надёжности по ответственности 1,0.',
        ];

        foreach ($lines as $line) {
            $section->addText($line, $body, $para);
        }

        $section->addTextBreak(1);
    }
}
