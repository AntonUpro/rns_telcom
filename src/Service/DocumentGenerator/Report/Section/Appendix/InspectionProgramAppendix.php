<?php

declare(strict_types=1);

namespace App\Service\DocumentGenerator\Report\Section\Appendix;

use App\Service\DocumentGenerator\DocStyleRegistry;
use App\Service\DocumentGenerator\Report\ReportContext;
use App\Service\DocumentGenerator\Report\SectionBuilderInterface;
use PhpOffice\PhpWord\Element\Section;
use PhpOffice\PhpWord\Style\ListItem;

/**
 * Приложение «Программа проведения обследования».
 */
final class InspectionProgramAppendix implements SectionBuilderInterface
{
    public function build(Section $section, ReportContext $context): void
    {
        $body = DocStyleRegistry::bodyText();
        $indent = DocStyleRegistry::paragraphIndent();

        $items = [
            'Выполнен сбор и анализ проектной, исполнительной и эксплуатационно-технической документации (при наличии).',
            'Рассмотрены фактические условия работы сооружения:',
        ];

        foreach ($items as $i => $item) {
            $section->addText($item, $body, $indent);
        }

        $subItems = [
            'место расположения объекта;',
            'природно-климатические условия;',
            'среда эксплуатации;',
            'воздействие на сооружение ветровых, снеговых, гололёдных и сейсмических нагрузок, '
            . 'а также нагрузок от установленного на сооружение технологического оборудования;',
            'рассмотрение воздействия на сооружение близлежащих строительных инженерно-технических сооружений.',
        ];

        foreach ($subItems as $sub) {
            $section->addListItem($sub, 0, $body, ['listType' => ListItem::TYPE_BULLET_FILLED]);
        }

        $section->addText(
            'Выполнены поверочные расчёты конструкций с учётом расчётных нагрузок, '
            . 'а также с учётом дефектов и повреждений конструкций, выявленных по результатам натурного обследования.',
            $body,
            $indent,
        );

        $section->addTextBreak(1);
    }
}
