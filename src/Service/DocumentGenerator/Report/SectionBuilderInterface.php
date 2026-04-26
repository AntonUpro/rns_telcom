<?php

declare(strict_types=1);

namespace App\Service\DocumentGenerator\Report;

use PhpOffice\PhpWord\Element\Section;

interface SectionBuilderInterface
{
    public function build(Section $section, ReportContext $context): void;
}
