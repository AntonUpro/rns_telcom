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
        $data    = $context->getData();
        $height  = $context->getHeightM();
        $address = $context->getAddress();
        $amsType = $data?->getAmsType() ?? '—';

        $body = DocStyleRegistry::bodyText();
        $para = DocStyleRegistry::paragraphIndent();

        $section->addText(
            sprintf(
                'Ствол опоры Н=%s м представляет собой %s, установленную по адресу: %s.',
                $height,
                $amsType,
                $address,
            ),
            $body,
            $para,
        );
        $section->addTextBreak(1);

        $section->addText(
            'Для подъёма на опору предусмотрена вертикальная лестница, закреплённая к стволу опоры '
            . 'с помощью стягивания хомутов.',
            $body,
            $para,
        );
        $section->addTextBreak(1);

        $section->addText(
            'Существующая кабельная трасса проложена по существующим кабельным полкам, '
            . 'закреплённым к конструкциям лестницы.',
            $body,
            $para,
        );
        $section->addTextBreak(1);
    }
}
