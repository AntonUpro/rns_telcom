<?php

declare(strict_types=1);

namespace App\Service\DocumentGenerator\Report\Section\Tower;

use App\Entity\CalculationImage;
use App\Service\DocumentGenerator\DocStyleRegistry;
use App\Service\DocumentGenerator\Report\ReportContext;
use App\Service\DocumentGenerator\Report\SectionBuilderInterface;
use PhpOffice\PhpWord\Element\Section;
use PhpOffice\PhpWord\Shared\Converter;
use PhpOffice\PhpWord\SimpleType\Jc;

/**
 * Раздел «Программный расчёт опоры» для башни: расчётная схема и мозаики
 * усилий / перемещений из ПК «ЛИРА-САПР», каждая на отдельной странице.
 */
final class TowerProgramCalculationSection implements SectionBuilderInterface
{
    /** Тип изображения => подпись над ним */
    private const IMAGES = [
        CalculationImage::TYPE_SCHEME_PC => 'Расчётная схема',
        CalculationImage::TYPE_MOSAIC_N_FACE => 'Мозаика максимальных усилий в элементах башни при действии расчётных ветровых нагрузок на грань опоры',
        CalculationImage::TYPE_MOSAIC_N_EDGE => 'Мозаика максимальных усилий в элементах башни при действии расчётных ветровых нагрузок на ребро опоры',
        CalculationImage::TYPE_MOSAIC_DISPLACEMENT_FACE => 'Мозаика максимальных отклонений ствола башни от вертикали при действии нормативных ветровых нагрузок на грань опоры',
        CalculationImage::TYPE_MOSAIC_DISPLACEMENT_EDGE => 'Мозаика максимальных отклонений ствола башни от вертикали при действии нормативных ветровых нагрузок на ребро опоры',
    ];

    public function build(Section $section, ReportContext $context, int &$tableNum): void
    {
        $isFirst = true;
        foreach (self::IMAGES as $type => $caption) {
            if (! $isFirst) {
                $section->addPageBreak();
            }
            $isFirst = false;

            $section->addText($caption, DocStyleRegistry::bodyText(), ['alignment' => Jc::CENTER]);

            $image = $context->getCalculationImageByType($type);
            if ($image === null || ! file_exists($image->getFilePath())) {
                $section->addText('[Изображение не загружено]', DocStyleRegistry::bodyText(), ['alignment' => Jc::CENTER]);
                continue;
            }

            $section->addImage($image->getFilePath(), [
                'height' => Converter::cmToPoint(22),
                'wrappingStyle' => 'inline',
                'alignment' => Jc::CENTER,
            ]);
        }
    }
}
