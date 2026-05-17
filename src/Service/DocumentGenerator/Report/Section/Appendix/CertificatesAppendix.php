<?php

declare(strict_types=1);

namespace App\Service\DocumentGenerator\Report\Section\Appendix;

use App\Enum\AppendixTypeEnum;
use App\Service\DocumentGenerator\Report\ReportContext;
use App\Service\DocumentGenerator\Report\SectionBuilderInterface;
use PhpOffice\PhpWord\Element\Section;
use PhpOffice\PhpWord\Shared\Converter;
use PhpOffice\PhpWord\SimpleType\Jc;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/** Приложение «Сертификаты» — изображения из БД, по одному на страницу. */
final class CertificatesAppendix implements SectionBuilderInterface
{
    public function __construct(
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir,
    ) {
    }

    public function build(Section $section, ReportContext $context): void
    {
        $images = $context->getAppendixImages(AppendixTypeEnum::CERTIFICATES);

        foreach ($images as $i => $image) {
            if ($i > 0) {
                $section->addPageBreak();
            }

            $absolutePath = $this->projectDir . '/var/uploads/appendix_images/' . $image->getFileName();
            if (!file_exists($absolutePath)) {
                continue;
            }

            $section->addImage($absolutePath, [
                'width'         => Converter::cmToPoint(17),
                'height'        => Converter::cmToPoint(25),
                'wrappingStyle' => 'inline',
                'alignment'     => Jc::CENTER,
            ]);
        }
    }
}
