<?php

declare(strict_types=1);

namespace App\Service\DocumentGenerator\Report\Section\Appendix;

use App\Service\DocumentGenerator\DocStyleRegistry;
use App\Service\DocumentGenerator\Report\ReportContext;
use App\Service\DocumentGenerator\Report\SectionBuilderInterface;
use PhpOffice\PhpWord\Element\Section;

/**
 * Приложение «Классификация условных обозначений» для башни.
 * Обозначения сечений совпадают с символами в таблицах отчёта.
 */
final class TowerSymbolsClassificationAppendix implements SectionBuilderInterface
{
    private const SYMBOLS = [
        'Кλ'      => 'коэффициент относительного удлинения;',
        'Сх'      => 'аэродинамический коэффициент лобового сопротивления;',
        'Сx∞'     => 'коэффициент, учитывающий параметры сечения элемента;',
        'µ'       => 'коэффициент затенения;',
        'γf'      => 'коэффициент надёжности по нагрузке;',
        '○'       => 'сечение в виде круглой трубы;',
        '□'       => 'сечение в виде квадратной трубы;',
        '└'       => 'сечение в виде одиночного уголка;',
        '∟∟'      => 'сечение в виде спаренных уголков;',
        '●'       => 'сечение в виде круга;',
        '['       => 'сечение в виде швеллера;',
        'А'       => 'наветренная площадь;',
        'φ1'      => 'коэффициент проницаемости;',
        'φ'       => 'коэффициент устойчивости;',
        'w₀'      => 'нормативное значение ветрового давления;',
        'Р'       => 'расчётное ветровое давление;',
        'b'       => 'толщина стенки гололёда;',
        'k'       => 'коэффициент, учитывающий изменение толщины стенки гололёда по высоте;',
        'μ2'      => 'коэффициент, учитывающий отношение площади поверхности элемента, подверженной обледенению, к полной площади поверхности элемента и принимаемый равным 0,6;',
        'ρ'       => 'плотность льда;',
        'g'       => 'ускорение свободного падения;',
        'I'       => 'момент инерции сечения;',
        'i'       => 'радиус инерции сечения;',
        'λ'       => 'гибкость элемента;',
        'Lef'     => 'расчётная длина элемента;',
        'Nрасч'   => 'расчётное усилие, воспринимаемое элементом;',
        'Nпред'   => 'предельное усилие, воспринимаемое элементом;',
        'Кисп'    => 'коэффициент использования (исчерпания) несущей способности;',
        'k₀'      => 'коэффициент надёжности по нагрузке на анкерные болты;',
        'σ'       => 'напряжения, возникающие в элементе;',
        'Ry'      => 'расчётное сопротивление стали.',
    ];

    public function build(Section $section, ReportContext $context, int &$tableNum): void
    {
        $bodyBold = DocStyleRegistry::bodyTextBold();
        $body     = DocStyleRegistry::bodyText();
        $para     = DocStyleRegistry::paragraphLeft();

        foreach (self::SYMBOLS as $symbol => $desc) {
            $textRun = $section->addTextRun($para);
            $textRun->addText($symbol . ' ', $bodyBold);
            $textRun->addText('— ' . $desc, $body);
        }

        $section->addTextBreak(1);
    }
}
