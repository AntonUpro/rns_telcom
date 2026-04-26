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
            'Определение технического состояния конструкций опоры и локальных конструкций здания, '
            . 'проведение поверочных расчётов напряжённо-деформированного состояния металлоконструкций '
            . 'антенной опоры для оценки возможности опоры воспринять нагрузку от оборудования. '
            . 'Определение возможности дальнейшей её эксплуатации или необходимости усиления.',
            $body,
            $para,
        );
        $section->addTextBreak(1);
    }
}
