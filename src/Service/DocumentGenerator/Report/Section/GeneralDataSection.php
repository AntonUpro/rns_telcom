<?php

declare(strict_types=1);

namespace App\Service\DocumentGenerator\Report\Section;

use App\Service\DocumentGenerator\DocStyleRegistry;
use App\Service\DocumentGenerator\Report\ReportContext;
use App\Service\DocumentGenerator\Report\SectionBuilderInterface;
use PhpOffice\PhpWord\Element\Section;

/**
 * Раздел «Общие данные»:
 * информация о заказчике, адресе и высоте опоры.
 */
final class GeneralDataSection implements SectionBuilderInterface
{
    public function build(Section $section, ReportContext $context): void
    {
        $data     = $context->getData();
        $customer = $data?->getCustomer()?->getName() ?? '—';
        $height   = $context->getHeightM();
        $address  = $context->getAddress();

        $body  = DocStyleRegistry::bodyText();
        $para  = DocStyleRegistry::paragraphIndent();
        $left  = DocStyleRegistry::paragraphLeft();

        $section->addText(
            sprintf(
                'Данное заключение выполнено по заказу %s для опоры Н=%s м, '
                . 'установленной по адресу: %s.',
                $customer,
                $height,
                $address,
            ),
            $body,
            $para,
        );
        $section->addTextBreak(1);
    }
}
