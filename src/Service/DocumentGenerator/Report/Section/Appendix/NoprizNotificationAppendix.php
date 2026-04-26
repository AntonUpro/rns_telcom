<?php

declare(strict_types=1);

namespace App\Service\DocumentGenerator\Report\Section\Appendix;

use App\Service\DocumentGenerator\Report\ReportContext;
use App\Service\DocumentGenerator\Report\SectionBuilderInterface;
use PhpOffice\PhpWord\Element\Section;

/** Приложение «Уведомление НОПРИЗ» — только заголовок. */
final class NoprizNotificationAppendix implements SectionBuilderInterface
{
    public function build(Section $section, ReportContext $context): void {}
}
