<?php

declare(strict_types=1);

namespace App\Service\DocumentGenerator;

use App\Exception\NotFoundException;
use App\Service\Calculation\Equipment\CalculationWindEquipmentService;
use App\Service\Calculation\Pillar\Pillar\PillarWindLoadCalculationService;
use App\Service\Calculation\PillarPlatform\PillarPlatformCalculationService;
use App\Service\DocumentGenerator\Table\EquipmentWindPressureTableBuilder;
use App\Service\DocumentGenerator\Table\PillarSectionsTableBuilder;
use App\Service\DocumentGenerator\Table\PlatformSectionsTableBuilder;
use PhpOffice\PhpWord\IOFactory;

/**
 * Оркестрирует генерацию DOCX-отчёта по расчёту.
 *
 * Структура документа:
 *   1. Ветровое давление на ствол опоры и коммуникации
 *   2. Ветровое давление на навесное оборудование
 *   3. Ветровое давление на площадку и подкосы
 *
 * @throws NotFoundException если данных недостаточно для генерации
 */
final readonly class CalculationReportGenerator
{
    public function __construct(
        private PillarWindLoadCalculationService  $pillarWindLoadService,
        private CalculationWindEquipmentService   $equipmentWindService,
        private PillarPlatformCalculationService  $platformService,
        private PillarSectionsTableBuilder        $pillarSectionsBuilder,
        private EquipmentWindPressureTableBuilder  $equipmentBuilder,
        private PlatformSectionsTableBuilder      $platformBuilder,
    ) {
    }

    /**
     * Генерирует отчёт и сохраняет DOCX-файл.
     *
     * @param int    $calculationId ID расчёта
     * @param string $outputDir     Директория для сохранения файла
     *
     * @return string Абсолютный путь к созданному файлу
     *
     * @throws NotFoundException если данных недостаточно для расчёта
     */
    public function generate(int $calculationId, string $outputDir): string
    {
        $pillarSections = $this->pillarWindLoadService->calculate($calculationId);
        $equipmentData  = $this->equipmentWindService->calculate($calculationId);

        $phpWord = IOFactory::load(__DIR__ . '/../../../template_doc/template.docx');
        $phpWord->setDefaultFontName('Times New Roman');
        $phpWord->setDefaultFontSize(10);

        $section = $phpWord->getSection(0);

        // ── 1. Ствол опоры + коммуникации ────────────────────────────────────
        $this->pillarSectionsBuilder->build($section, $pillarSections);
        $section->addTextBreak(2);

        // ── 2. Навесное оборудование ──────────────────────────────────────────
        $this->equipmentBuilder->build($section, $equipmentData);
        $section->addTextBreak(2);

        // ── 3. Площадка и подкосы (опционально) ──────────────────────────────
        try {
            $platformData = $this->platformService->calculatePillarPlatform($calculationId);
            if (!empty($platformData->platformSections)) {
                $this->platformBuilder->build($section, $platformData);
            }
        } catch (NotFoundException) {
            // Если площадки нет — секция пропускается
        }

        // ── Сохранение ────────────────────────────────────────────────────────
        if (!is_dir($outputDir) && !mkdir($outputDir, 0755, true)) {
            throw new \RuntimeException(sprintf('Не удалось создать директорию "%s"', $outputDir));
        }

        $filePath = sprintf('%s/calculation_%d.docx', rtrim($outputDir, '/'), $calculationId);

        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($filePath);

        return $filePath;
    }
}
