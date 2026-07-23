<?php

declare(strict_types=1);

namespace App\Service\DocumentGenerator\Report\Section;

use App\Service\DocumentGenerator\DocStyleRegistry;
use App\Service\DocumentGenerator\Report\ReportContext;
use App\Service\DocumentGenerator\Report\SectionBuilderInterface;
use PhpOffice\PhpWord\Element\Section;

/**
 * Раздел «Конструктивное решение сооружения».
 * Описание опоры на основе amsType и amsHeight из CalculationData.
 */
final class StructuralSection implements SectionBuilderInterface
{
    public function build(Section $section, ReportContext $context, int &$tableNum): void
    {
        $data = $context->getData();
        $height = $context->getHeightM();
        $heightPillar = $context->calculation?->getCalculationData()?->getConcretePillarSpecificData()?->pillarHeight ?? 0;
        $amsType = $data?->getConcretePillarSpecificData()?->pillarStamp ?? '—';

        $existStrut = $context->calculation?->getPillarPlatform()->existStrut() ?? '—';

        $body = DocStyleRegistry::bodyText();
        $para = DocStyleRegistry::paragraphIndent();

        $section->addText(
            sprintf(
                'Столб %s по ГОСТ 22687 представляет собой железобетонную коническую стойку, выполненную из преднапряженной и не напряженной арматуры, кольцевого сечения, изготовляемую методом центрифугирования из тяжелого бетона марки В40.',
                $amsType,
            ),
            $body,
            $para,
        );

        $section->addText(
            'Опора предназначена для размещения антенного оборудования сотовой связи.',
            $body,
            $para,
        );

        $pillarInfo = sprintf(
            'На отм. +%.3f м установлена площадка с надстройкой для размещения и обслуживания оборудования. Площадка закреплена к стволу опоры при помощи металлического оголовка',
            $heightPillar,
        );
        if ($existStrut) {
            $pillarInfo .= ' и системы подкосов';
        }
        $pillarInfo .= '.';

        $section->addText($pillarInfo, $body, $para);

        $section->addText('Для подъема на опору предусмотрена вертикальная лестница с корзиной ограждения.', $body, $para);
        $section->addText('Кабельная трасса прокладывается параллельно лестнице.', $body, $para);

        if ($data?->getConcretePillarSpecificData()?->strengtheningExist) {
            $section->addText(
                sprintf(
                    'Произведено усиление железобетонной обоймы путем увеличения расчетного сечения стойки, размеры в плане %.1fx%.1f до высоты Н= %.1f м',
                    $data?->getConcretePillarSpecificData()?->strengthening->strengtheningWidth ?? 0,
                    $data?->getConcretePillarSpecificData()?->strengthening->strengtheningWidth ?? 0,
                    $data?->getConcretePillarSpecificData()?->strengthening->strengtheningHeight ?? 0,
                ),
                $body,
                $para,
            );
        }

    }
}
