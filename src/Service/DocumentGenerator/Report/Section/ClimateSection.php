<?php

declare(strict_types=1);

namespace App\Service\DocumentGenerator\Report\Section;

use App\Service\DocumentGenerator\DocStyleRegistry;
use App\Service\DocumentGenerator\Report\ReportContext;
use App\Service\DocumentGenerator\Report\SectionBuilderInterface;
use PhpOffice\PhpWord\Element\Section;

/**
 * Раздел «Географические параметры и климатические условия».
 * Данные берутся из CalculationData: ветровой, снеговой, гололёдный районы, тип местности.
 */
final class ClimateSection implements SectionBuilderInterface
{
    public function build(Section $section, ReportContext $context): void
    {
        $data = $context->getData();
        $body = DocStyleRegistry::bodyText();
        $para = DocStyleRegistry::paragraphLeft();
        $ind  = DocStyleRegistry::paragraphIndent();

        $section->addTextBreak(1);
        $section->addText('Расположение объекта:', $body, $ind);

        // Тип местности
        $terrainType = $data?->getTerrainType();
        $terrainLabel = $terrainType !== null
            ? $terrainType->value
            : '—';
        $section->addText('• тип местности ' . $terrainLabel . ';', $body, $para);

        // Ветровой район
        $windRegion = $data?->getWindRegion();
        if ($windRegion !== null) {
            $section->addText(
                sprintf(
                    '• ветровой район %s, нормативная ветровая нагрузка %d кгс/м² (%d Па);',
                    $windRegion->value,
                    $windRegion->pressureKgPerM(),
                    $windRegion->pressure(),
                ),
                $body,
                $para,
            );
        } else {
            $section->addText('• ветровой район —;', $body, $para);
        }

        $section->addText(
            '• коэффициент надёжности для ветровой нагрузки 1,4;',
            $body,
            $para,
        );

        // Гололёдный район
        $icingRegion = $data?->getIcingRegion();
        if ($icingRegion !== null) {
            $thickness = $icingRegion->thicknessMm();
            $section->addText(
                sprintf(
                    '• гололёдный район %s (%s), толщина стенки гололёда не менее %d мм;',
                    $icingRegion->value,
                    $icingRegion->description(),
                    $thickness['min'],
                ),
                $body,
                $para,
            );
        } else {
            $section->addText('• гололёдный район —;', $body, $para);
        }

        $section->addText(
            '• коэффициент надёжности для гололёдной нагрузки 1,8;',
            $body,
            $para,
        );

        // Снеговой район
        $snowRegion = $data?->getSnowRegion();
        if ($snowRegion !== null) {
            $snowKgm2 = (int) round($snowRegion->snowLoad() * 100);
            $section->addText(
                sprintf(
                    '— снеговой район %s, нормативный вес снегового покрова %d кгс/м²;',
                    $snowRegion->value,
                    $snowKgm2,
                ),
                $body,
                $para,
            );
        } else {
            $section->addText('• снеговой район —;', $body, $para);
        }

        $section->addText(
            '• коэффициент надёжности для снеговой нагрузки 1,4.',
            $body,
            $para,
        );

        $section->addTextBreak(1);
    }
}
