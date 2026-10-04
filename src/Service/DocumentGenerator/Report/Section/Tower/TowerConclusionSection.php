<?php

declare(strict_types=1);

namespace App\Service\DocumentGenerator\Report\Section\Tower;

use App\Service\DocumentGenerator\DocStyleRegistry;
use App\Service\DocumentGenerator\Report\ReportContext;
use App\Service\DocumentGenerator\Report\Section\SignatureBlock;
use App\Service\DocumentGenerator\Report\SectionBuilderInterface;
use PhpOffice\PhpWord\Element\Section;
use PhpOffice\PhpWord\Element\TextRun;

/**
 * Раздел «Заключение» для башни: вывод о несущей способности конструкций,
 * деформациях и фундаментах по результатам расчёта, перечень необходимых усилений.
 */
final class TowerConclusionSection implements SectionBuilderInterface
{
    public function build(Section $section, ReportContext $context, int &$tableNum): void
    {
        $body = DocStyleRegistry::bodyText();
        $para = DocStyleRegistry::paragraphIndent();

        $structureFails = $this->exceeds($context->getTowerStructureMaxK());
        $deformationFails = $this->exceeds($context->getTowerDeformationMaxK());
        $foundationKuse = $context->getTowerFoundationMaxK();

        $text = $section->addTextRun($para);
        $text->addText(
            sprintf(
                'Поверочный расчёт конструкций башни Н=%s м, расположенной по адресу: %s, показал, '
                . 'что несущая способность конструкций опоры при воздействии расчётных нагрузок ',
                $context->getHeightM(),
                $context->getAddress(),
            ),
            $body,
        );
        $this->addVerdict($text, $structureFails, 'соответствует', 'не соответствует');
        $text->addText(' требованиям нормативной документации. Деформации конструкций ствола опоры при воздействии нормативных нагрузок ', $body);
        $this->addVerdict($text, $deformationFails, 'соответствуют', 'не соответствуют');
        $text->addText(' требованиям нормативной документации.', $body);

        if ($foundationKuse !== null) {
            $text = $section->addTextRun($para);
            $text->addText('Несущая способность фундаментов ', $body);
            $this->addVerdict($text, $this->exceeds($foundationKuse), 'соответствует', 'не соответствует');
            $text->addText(' требованиям нормативной документации.', $body);
        }

        $section->addTextBreak(1);

        $negativeCalculations = $context->getTowerNegativeCalculations();
        if ($negativeCalculations !== []) {
            $text = $section->addTextRun($para);
            $text->addText('Модернизация антенно-фидерного оборудования ', $body);
            $text->addText('не допускается', DocStyleRegistry::titleTableTextUnderlineBold());
            $text->addText(' без проведения компенсирующих мероприятий.', $body);
            $text->addText(' Метод и объем усиления определить проектом на усиление, разработанным специализированной организацией, имеющей соответствующую Лицензию:', $body);

            // Одинаковые формулировки (например, растяжение и срез фланцевых болтов) выводим один раз
            $formulations = array_unique(array_map(
                static fn($type): string => $type->constructFormulation(),
                $negativeCalculations,
            ));
            foreach ($formulations as $formulation) {
                $section->addText('– ' . $formulation . ';', $body, $para);
            }
        } else {
            $section->addText(
                'Модернизация антенно-фидерного оборудования допускается без проведения компенсирующих мероприятий.',
                $body,
                $para,
            );
        }

        $section->addTextBreak(2);

        (new SignatureBlock())->build($section, $context);
    }

    private function exceeds(?float $kUse): bool
    {
        return $kUse !== null && $kUse > 1.0;
    }

    private function addVerdict(TextRun $text, bool $fails, string $positive, string $negative): void
    {
        $text->addText(
            $fails ? $negative : $positive,
            $fails ? DocStyleRegistry::titleTableTextUnderlineBold() : DocStyleRegistry::titleTableTextUnderline(),
        );
    }
}
