<?php

declare(strict_types=1);

namespace App\Service\DocumentGenerator\Report\Section;

use App\Enum\Calculation\ResultTableTypeEnum;
use App\Service\Calculation\PillarByHeight\SimpleCalculator;
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
            $topReinforcementMark = $this->findTopReinforcementMark($context);

            $textRunModern = $section->addTextRun($para);
            $textRunModern->addText('Модернизация антенно-фидерного оборудования ', $body);
            $textRunModern->addText('не допускается', DocStyleRegistry::titleTableTextUnderlineBold());
            $textRunModern->addText(' без проведения компенсирующих мероприятий.', $body);
            $textRunModern->addText(' Метод и объем усиления определить проектом на усиление, разработанным специализированной организацией, имеющей соответствующую Лицензию:', $body);
            foreach ($resultTableTypes as $type) {
                $mark = $type === ResultTableTypeEnum::PILLAR_FORCES ? $topReinforcementMark : null;
                $section->addText('– ' . $type->constructFormulation($mark) . ';', $body, $para);
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

    /**
     * Самая верхняя отметка (м) в расчёте несущей способности ствола опоры,
     * на которой коэффициент использования Кисп ещё ≥ 1 (считаем снизу вверх).
     */
    private function findTopReinforcementMark(ReportContext $context): ?float
    {
        $topMark = null;
        foreach ((new SimpleCalculator())->calculate($context) as $row) {
            if ($row->k >= 1) {
                $topMark = $row->mark;
            }
        }

        return $topMark;
    }
}
