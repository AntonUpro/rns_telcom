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
 * Раздел «Схема опоры» — изображения из calculation_report_file типа TYPE_PILLAR.
 */
final class PillarSchemeSection implements SectionBuilderInterface
{
    public function build(Section $section, ReportContext $context): void
    {
        $files = $context->getReportFilesByType(CalculationReportFile::TYPE_PILLAR);

        if (empty($files)) {
            $section->addText(
                '[Схема опоры не загружена]',
                DocStyleRegistry::bodyText(),
                ['alignment' => Jc::CENTER],
            );
            $section->addTextBreak(1);
            return;
        }

        foreach ($files as $file) {
            $path = $file->getFilePath();
            if (!file_exists($path)) {
                continue;
            }

            $section->addImage($path, [
                'width'          => Converter::cmToPoint(15),
                'height'         => Converter::cmToPoint(20),
                'wrappingStyle'  => 'inline',
                'alignment'      => Jc::CENTER,
            ]);
            $section->addTextBreak(1);
        }
    }
}
