<?php

declare(strict_types=1);

namespace App\Service\DocumentGenerator\Report\Section;

use App\Service\DocumentGenerator\DocStyleRegistry;
use App\Service\DocumentGenerator\Report\ReportContext;
use App\Service\DocumentGenerator\Report\SectionBuilderInterface;
use PhpOffice\PhpWord\Element\Section;

/**
 * Раздел «Цель проведения расчёта и обследования» — стандартный текст.
 */
final class PurposeSection implements SectionBuilderInterface
{
    public function build(Section $section, ReportContext $context): void
    {
        $body = DocStyleRegistry::bodyText();
        $para = DocStyleRegistry::paragraphIndent();

        $section->addText(
            'Проведение расчетов напряженно-деформированного состояния конструкций антенной опоры для оценки возможности опоры воспринять нагрузку от оборудования. Определение возможности дальнейшей ее эксплуатации или необходимости усиления.',
            $body,
            $para,
        );
        $section->addText(
            'Определение технического состояния конструкций, проведение поверочных расчетов напряженно-деформированного состояния металлоконструкций антенной опоры для оценки возможности опоры воспринять нагрузку от существующего и планируемого к размещению оборудования. Определение возможности дальнейшей эксплуатации опоры или необходимости ее усиления.',
            $body,
            $para,
        );
        $section->addTextBreak(1);
    }
}
