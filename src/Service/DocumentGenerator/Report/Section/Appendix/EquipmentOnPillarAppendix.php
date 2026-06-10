<?php

declare(strict_types=1);

namespace App\Service\DocumentGenerator\Report\Section\Appendix;

use App\Entity\CalculationImage;
use App\Service\DocumentGenerator\Report\ReportContext;
use App\Service\DocumentGenerator\Report\SectionBuilderInterface;
use PhpOffice\PhpWord\Element\Section;
use PhpOffice\PhpWord\Shared\Converter;
use PhpOffice\PhpWord\SimpleType\Jc;

/** Приложение «Перечень оборудования на опоре» — изображения из calculation_images. */
final class EquipmentOnPillarAppendix implements SectionBuilderInterface
{
    public function build(Section $section, ReportContext $context, int &$tableNum): void
    {
        $images = $context->getCalculationImagesByType(CalculationImage::TYPE_EQUIPMENT_LIST);

        foreach ($images as $i => $image) {
            if ($i > 0) {
                $section->addPageBreak();
            }

            if (!file_exists($image->getFilePath())) {
                continue;
            }

            $section->addImage($image->getFilePath(), [
                'width'         => Converter::cmToPoint(17),
                'height'        => Converter::cmToPoint(24),
                'wrappingStyle' => 'inline',
                'alignment'     => Jc::CENTER,
            ]);
        }
    }
}
