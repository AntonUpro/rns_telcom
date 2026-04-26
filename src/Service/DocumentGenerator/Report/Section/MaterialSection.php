<?php

declare(strict_types=1);

namespace App\Service\DocumentGenerator\Report\Section;

use App\Service\DocumentGenerator\DocStyleRegistry;
use App\Service\DocumentGenerator\Report\ReportContext;
use App\Service\DocumentGenerator\Report\SectionBuilderInterface;
use PhpOffice\PhpWord\Element\Section;

/**
 * Раздел «Характеристики материала конструкций» — стандартный текст.
 */
final class MaterialSection implements SectionBuilderInterface
{
    public function build(Section $section, ReportContext $context): void
    {
        $body = DocStyleRegistry::bodyText();
        $para = DocStyleRegistry::paragraphLeft();

        $lines = [
            '— модуль упругости стальных элементов 2,06 × 10⁵ Н/мм²;',
            '— модуль упругости железобетона 3,19 × 10⁴ Н/мм²;',
            '— расчётное сопротивление стали принято 225 Н/мм² и 240 Н/мм².',
        ];

        foreach ($lines as $line) {
            $section->addText($line, $body, $para);
        }

        $section->addTextBreak(1);
    }
}
