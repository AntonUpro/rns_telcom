<?php

declare(strict_types=1);

namespace App\Service\DocumentGenerator\Report\Section;

use App\Service\DocumentGenerator\DocStyleRegistry;
use App\Service\DocumentGenerator\Report\ReportContext;
use App\Service\DocumentGenerator\Report\SectionBuilderInterface;
use PhpOffice\PhpWord\Element\Section;
use PhpOffice\PhpWord\Style\ListItem;

/**
 * Раздел «Предоставленная документация» — список из таблицы calculation_documents.
 */
final class DocumentationSection implements SectionBuilderInterface
{
    public function build(Section $section, ReportContext $context): void
    {
        $body = DocStyleRegistry::bodyText();
        $para = DocStyleRegistry::paragraphIndent();
        $left = DocStyleRegistry::paragraphLeft();

        $section->addText(
            'Для выполнения расчета несущей способности опоры были предоставлены и использованы следующие документы:',
            $body,
            $para,
        );

        if (empty($context->documents)) {
            $section->addText('- документы не указаны.', $body, $para);
        } else {
            foreach ($context->documents as $i => $doc) {
                $section->addListItem(
                    $doc->getName(),
                    0,
                    $body,
                    ['listType' => ListItem::TYPE_BULLET_FILLED],
                    $left,
                );
            }
        }

        $section->addTextBreak(1);
    }
}
