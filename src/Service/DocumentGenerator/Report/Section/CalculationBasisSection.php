<?php

declare(strict_types=1);

namespace App\Service\DocumentGenerator\Report\Section;

use App\Service\DocumentGenerator\DocStyleRegistry;
use App\Service\DocumentGenerator\Report\ReportContext;
use App\Service\DocumentGenerator\Report\SectionBuilderInterface;
use PhpOffice\PhpWord\Element\Section;

/**
 * Раздел «Основные расчётные положения» — стандартный текст.
 */
final class CalculationBasisSection implements SectionBuilderInterface
{
    public function build(Section $section, ReportContext $context): void
    {
        $body = DocStyleRegistry::bodyText();
        $para = DocStyleRegistry::paragraphIndent();

        $paragraphs = [
            'Расчёт опоры выполнен в соответствии с требованиями нормативной документации, '
            . 'указанной в Приложении 1, с использованием программного комплекса для расчёта '
            . 'строительных конструкций.',

            'Расчётная схема принята на основании конструктивных особенностей сооружения, '
            . 'указанных в проектной документации и уточнённых по результатам натурного обследования.',

            'Нагрузки приняты в соответствии с требованиями СП 20.13330.2016 «Нагрузки и воздействия» '
            . 'с учётом климатических условий расположения объекта.',

            'Расчёты выполнены по первой (несущая способность) и второй (эксплуатационная пригодность) '
            . 'группам предельных состояний.',
        ];

        foreach ($paragraphs as $text) {
            $section->addText($text, $body, $para);
            $section->addTextBreak(1);
        }
    }
}
