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
        $left = DocStyleRegistry::paragraphLeft();

        $items = [
            'Выполнен сбор и анализ проектной, исполнительной и эксплуатационно-технической документации (при наличии).',
            'Рассмотрены фактические условия работы сооружения:',
        ];

        foreach ($items as $i => $item) {
            $section->addListItem(
                $item,
                0,
                $body,
                ['listType' => ListItem::TYPE_NUMBER],
                $left,
            );
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
            $section->addText('— ' . $sub, $body, $left);
        }

        $section->addListItem(
            'Выполнены поверочные расчёты конструкций с учётом расчётных нагрузок, '
            . 'а также с учётом дефектов и повреждений конструкций, выявленных по результатам натурного обследования.',
            0,
            $body,
            ['listType' => ListItem::TYPE_NUMBER],
            $left,
        );

        $section->addTextBreak(1);
    }
}
