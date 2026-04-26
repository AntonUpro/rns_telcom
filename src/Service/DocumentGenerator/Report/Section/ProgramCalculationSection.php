<?php

declare(strict_types=1);

namespace App\Service\DocumentGenerator\Report\Section;

use App\Entity\CalculationReportFile;
use App\Service\DocumentGenerator\DocStyleRegistry;
use App\Service\DocumentGenerator\Report\ReportContext;
use App\Service\DocumentGenerator\Report\SectionBuilderInterface;
use PhpOffice\PhpWord\Element\Section;
use PhpOffice\PhpWord\Shared\Converter;
use PhpOffice\PhpWord\SimpleType\Jc;

/**
 * Раздел «Программный расчёт опоры» — результирующие изображения из TYPE_EQUIPMENT файлов.
 */
final class ProgramCalculationSection implements SectionBuilderInterface
{
    public function build(Section $section, ReportContext $context): void
    {
        $body  = DocStyleRegistry::bodyText();
        $left  = DocStyleRegistry::paragraphLeft();
        $center = ['alignment' => Jc::CENTER];

        $section->addText(
            'Расчётная схема опоры и результаты программного расчёта приведены ниже.',
            $body,
            $left,
        );
        $section->addTextBreak(1);

        $files = $context->getReportFilesByType(CalculationReportFile::TYPE_EQUIPMENT);

        if (empty($files)) {
            $section->addText('[Изображения результатов расчёта не загружены]', $body, $center);
            $section->addTextBreak(1);
            return;
        }

        $captions = [
            'Расчётная схема опоры',
            'Мозаика максимальных усилий N (продольных сил) в стволе опоры',
            'Мозаика максимальных усилий Q (поперечных сил) в стволе опоры',
            'Мозаика максимальных усилий М (моментов) в стволе опоры',
            'Мозаика максимальных отклонений ствола опоры от вертикали',
        ];

        foreach ($files as $i => $file) {
            $path = $file->getFilePath();
            if (!file_exists($path)) {
                continue;
            }

            $section->addImage($path, [
                'width'         => Converter::cmToPoint(14),
                'height'        => Converter::cmToPoint(18),
                'wrappingStyle' => 'inline',
                'alignment'     => Jc::CENTER,
            ]);

            $caption = $captions[$i] ?? sprintf('Рисунок %d', $i + 1);
            $section->addText($caption, DocStyleRegistry::italicCenter(), $center);
            $section->addTextBreak(1);
        }
    }
}
