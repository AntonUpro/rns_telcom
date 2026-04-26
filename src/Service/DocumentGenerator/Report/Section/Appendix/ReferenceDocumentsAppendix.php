<?php

declare(strict_types=1);

namespace App\Service\DocumentGenerator\Report\Section\Appendix;

use App\Service\DocumentGenerator\DocStyleRegistry;
use App\Service\DocumentGenerator\Report\ReportContext;
use App\Service\DocumentGenerator\Report\SectionBuilderInterface;
use PhpOffice\PhpWord\Element\Section;

/**
 * Приложение «Ведомость ссылочных документов».
 */
final class ReferenceDocumentsAppendix implements SectionBuilderInterface
{
    private const DOCUMENTS = [
        ['СП 63.13330.2018',    'Бетонные и железобетонные конструкции. Основные положения'],
        ['СП 16.13330.2017',    'Стальные конструкции. Нормы проектирования'],
        ['СП 20.13330.2016',    'Нагрузки и воздействия. Актуализированная редакция СНиП 2.01.07-85*'],
        ['СП 131.13330.2018',   'Строительная климатология'],
        ['СП 70.13330.2012',    'Несущие и ограждающие конструкции'],
        ['СП 28.13330.2017',    'Защита строительных конструкций от коррозии'],
        ['СП 14.13330.2018',    'Строительство в сейсмических районах'],
        ['СП 47.13330.2016',    'Инженерные изыскания для строительства. Основные положения'],
        ['СП 43.13330.2012',    'Сооружения промышленных предприятий'],
        ['СП 13-102-2003',      'Правила обследования несущих строительных конструкций зданий и сооружений'],
        ['СП 53-102-2004',      'Общие правила проектирования стальных конструкций'],
        ['ГОСТ 31937-2011',     'Здания и сооружения. Правила обследования и мониторинга технического состояния'],
        ['ГОСТ 23118-2012',     'Конструкции стальные строительные. Общие технические условия'],
        ['ГОСТ 16350-80',       'Климат СССР. Районирование и статические параметры климатических факторов для технических целей'],
        ['ОСТ 45.091.350-91',   'Металлические мачты и башни радиопредприятий. Общие требования безопасности'],
        ['РД 03-606-03',        'Инструкция по визуальному и измерительному контролю'],
        ['ФЗ-384',              'Технический регламент о безопасности зданий и сооружений'],
        ['',                    'Руководство по расчёту зданий и сооружений на действие ветра. ЦНИИСК им. Кучеренко, 1978 г.'],
        ['',                    'Инструкция по эксплуатации антенных сооружений радиорелейных линий связи. Министерство связи СССР, 1980 г.'],
        ['',                    'Техническая документация на оборудование сотовой связи'],
    ];

    public function build(Section $section, ReportContext $context): void
    {
        $italic = DocStyleRegistry::italicCenter();
        $body   = DocStyleRegistry::bodyText();
        $center = DocStyleRegistry::paragraphCenter();
        $left   = DocStyleRegistry::paragraphLeft();

        $w   = [2500, 7500];
        $tbl = $section->addTable(DocStyleRegistry::tableStyleReport());

        $tbl->addRow(500);
        $tbl->addCell($w[0], DocStyleRegistry::headerCell())->addText('Обозначение', $italic, $center);
        $tbl->addCell($w[1], DocStyleRegistry::headerCell())->addText('Наименование', $italic, $center);

        foreach (self::DOCUMENTS as [$code, $name]) {
            $tbl->addRow(400);
            $tbl->addCell($w[0], DocStyleRegistry::dataCell())->addText($code, DocStyleRegistry::center(), $center);
            $tbl->addCell($w[1], DocStyleRegistry::dataCell())->addText($name, DocStyleRegistry::center(), $left);
        }

        $section->addTextBreak(1);
    }
}
