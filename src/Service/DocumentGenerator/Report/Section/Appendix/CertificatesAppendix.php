<?php

declare(strict_types=1);

namespace App\Service\DocumentGenerator\Report\Section\Appendix;

use App\Service\DocumentGenerator\Report\ReportContext;
use App\Service\DocumentGenerator\Report\SectionBuilderInterface;
use PhpOffice\PhpWord\Element\Section;

/** Приложение «Сертификаты» — только заголовок. */
final class CertificatesAppendix implements SectionBuilderInterface
{
    public function build(Section $section, ReportContext $context): void
    {
        // Заголовок уже добавлен генератором — тело пусто.
    }
}
