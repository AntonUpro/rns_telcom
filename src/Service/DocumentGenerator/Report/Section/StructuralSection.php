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
    public function build(Section $section, ReportContext $context): void
    {
        $data = $context->getData();
        $height = $context->getHeightM();
        $amsType = $data?->getAmsType() ?? '—';

        $body = DocStyleRegistry::bodyText();
        $para = DocStyleRegistry::paragraphIndent();

        $section->addText(
            sprintf(
                'Ствол опоры Н=%s м представляет собой железобетонную коническую центрифугированную стойку типа %s, с поперечным сечением в виде кольца переменного диаметра, защемленную в грунт.',
                $height,
                $amsType,
            ),
            $body,
            $para,
        );

        $section->addText(
            'Опора выполнена из железобетона с преднапрягаемой и ненапрягаемой арматурой. Опора предназначена для размещения антенного об орудования сотовой связи.',
            $body,
            $para,
        );

        $section->addText(
            'Опора предназначена для размещения антенного оборудования сотовой связи.',
            $body,
            $para,
        );

        $section->addText(
            sprintf('Для подъема на опору предусмотрена вертикальная лестница с корзиной ограждения, закрепленная к стволу опоры. Кабели проложены по кабельросту расположенному параллельно лестнице. ' .
                'На отметке +%.3f м установлена площадка для размещения и обслуживания антенного оборудования, закрепленная к опоре с помощью металлического оголовка.',
                $height,
            ),
            $body,
            $para,
        );
    }
}
