<?php

declare(strict_types=1);

namespace App\Service\DocumentGenerator\Report;

use App\Exception\NotFoundException;
use App\Repository\CalculationDocumentRepository;
use App\Repository\CalculationRepository;
use App\Repository\CalculationReportFileRepository;
use App\Repository\CalculationResultTableRepository;
use App\Service\DocumentGenerator\DocStyleRegistry;
use App\Service\DocumentGenerator\Report\Section\Appendix\CertificatesAppendix;
use App\Service\DocumentGenerator\Report\Section\Appendix\EquipmentListAppendix;
use App\Service\DocumentGenerator\Report\Section\Appendix\InspectionProgramAppendix;
use App\Service\DocumentGenerator\Report\Section\Appendix\NoprizNotificationAppendix;
use App\Service\DocumentGenerator\Report\Section\Appendix\ReferenceDocumentsAppendix;
use App\Service\DocumentGenerator\Report\Section\Appendix\SroExcerptAppendix;
use App\Service\DocumentGenerator\Report\Section\Appendix\SymbolsClassificationAppendix;
use App\Service\DocumentGenerator\Report\Section\Appendix\TermsClassificationAppendix;
use App\Service\DocumentGenerator\Report\Section\CalculationBasisSection;
use App\Service\DocumentGenerator\Report\Section\CalculationResultsSection;
use App\Service\DocumentGenerator\Report\Section\ClimateSection;
use App\Service\DocumentGenerator\Report\Section\ConclusionSection;
use App\Service\DocumentGenerator\Report\Section\DocumentationSection;
use App\Service\DocumentGenerator\Report\Section\GeneralDataSection;
use App\Service\DocumentGenerator\Report\Section\MaterialSection;
use App\Service\DocumentGenerator\Report\Section\PillarSchemeSection;
use App\Service\DocumentGenerator\Report\Section\ProgramCalculationSection;
use App\Service\DocumentGenerator\Report\Section\PurposeSection;
use App\Service\DocumentGenerator\Report\Section\StructuralSection;
use App\Service\DocumentGenerator\Report\Section\VerticalLoadsSection;
use App\Service\DocumentGenerator\Report\Section\WindLoadsSection;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Shared\Converter;
use PhpOffice\PhpWord\SimpleType\Jc;

/**
 * Генерирует полный отчёт ОТС (обследование технического состояния) в формате DOCX.
 *
 * Структура документа:
 *   Содержание
 *   1. Общие данные
 *   2. Цель проведения расчёта и обследования
 *   3. Предоставленная документация
 *   4. Географические параметры и климатические условия
 *   5. Характеристики материала конструкций
 *   6. Конструктивное решение сооружения
 *   7. Схема опоры
 *   8. Горизонтальные нагрузки (8.1 + 8.2)
 *   9. Вертикальные нагрузки (9.1)
 *   10. Основные расчётные положения
 *   11. Программный расчёт опоры
 *   12. Результаты расчёта и выводы
 *   13. Заключение
 *   Приложения 1–8
 */
final readonly class OtsReportGenerator
{
    public function __construct(
        private CalculationRepository           $calculationRepository,
        private CalculationDocumentRepository   $documentRepository,
        private CalculationResultTableRepository $resultTableRepository,
        private CalculationReportFileRepository $reportFileRepository,
        private WindLoadsSection                $windLoadsSection,
    ) {}

    /**
     * @throws NotFoundException     если расчёт не найден
     * @throws \RuntimeException     если не удалось создать директорию или сохранить файл
     */
    public function generate(int $calculationId, string $outputDir): string
    {
        $calculation = $this->calculationRepository->findById($calculationId);
        if ($calculation === null) {
            throw new NotFoundException(sprintf('Расчёт #%d не найден', $calculationId));
        }

        $context = new ReportContext(
            calculation:  $calculation,
            documents:    $this->documentRepository->findByCalculation($calculationId),
            resultTables: $this->resultTableRepository->findAllByCalculationIndexed($calculation),
            reportFiles:  $this->reportFileRepository->findByCalculation($calculationId),
        );

        $phpWord = $this->createDocument();
        $section = $phpWord->getSection(0);

        // ── Содержание ────────────────────────────────────────────────────────
        $section->addTitle('СОДЕРЖАНИЕ', 1);
        $section->addTOC(['size' => 10, 'name' => 'Times New Roman'], [], 1, 2);
        $section->addPageBreak();

        // ── Разделы 1–13 ──────────────────────────────────────────────────────
        $this->addHeading1($section, '1. ОБЩИЕ ДАННЫЕ');
        (new GeneralDataSection())->build($section, $context);

        $this->addHeading1($section, '2. ЦЕЛЬ ПРОВЕДЕНИЯ РАСЧЁТА И ОБСЛЕДОВАНИЯ');
        (new PurposeSection())->build($section, $context);

        $this->addHeading1($section, '3. ПРЕДОСТАВЛЕННАЯ ДОКУМЕНТАЦИЯ');
        (new DocumentationSection())->build($section, $context);

        $this->addHeading1($section, '4. ГЕОГРАФИЧЕСКИЕ ПАРАМЕТРЫ И КЛИМАТИЧЕСКИЕ УСЛОВИЯ РАСПОЛОЖЕНИЯ СООРУЖЕНИЯ');
        (new ClimateSection())->build($section, $context);

        $this->addHeading1($section, '5. ХАРАКТЕРИСТИКИ МАТЕРИАЛА КОНСТРУКЦИЙ');
        (new MaterialSection())->build($section, $context);

        $this->addHeading1($section, '6. КОНСТРУКТИВНОЕ РЕШЕНИЕ СООРУЖЕНИЯ');
        (new StructuralSection())->build($section, $context);

        $this->addHeading1($section, '7. СХЕМА ОПОРЫ');
        (new PillarSchemeSection())->build($section, $context);
        $section->addPageBreak();

        $this->addHeading1($section, '8. ГОРИЗОНТАЛЬНЫЕ НАГРУЗКИ');
        $this->windLoadsSection->build($section, $context);
        $section->addPageBreak();

        $this->addHeading1($section, '9. ВЕРТИКАЛЬНЫЕ НАГРУЗКИ');
        (new VerticalLoadsSection())->build($section, $context);

        $this->addHeading1($section, '10. ОСНОВНЫЕ РАСЧЁТНЫЕ ПОЛОЖЕНИЯ');
        (new CalculationBasisSection())->build($section, $context);

        $this->addHeading1($section, '11. ПРОГРАММНЫЙ РАСЧЁТ ОПОРЫ');
        (new ProgramCalculationSection())->build($section, $context);
        $section->addPageBreak();

        $this->addHeading1($section, '12. РЕЗУЛЬТАТЫ РАСЧЁТА И ВЫВОДЫ');
        (new CalculationResultsSection())->build($section, $context);
        $section->addPageBreak();

        $this->addHeading1($section, '13. ЗАКЛЮЧЕНИЕ');
        (new ConclusionSection())->build($section, $context);
        $section->addPageBreak();

        // ── Приложения ────────────────────────────────────────────────────────
        $this->addHeading1($section, 'ПРИЛОЖЕНИЕ 1. ВЕДОМОСТЬ ССЫЛОЧНЫХ ДОКУМЕНТОВ');
        (new ReferenceDocumentsAppendix())->build($section, $context);
        $section->addPageBreak();

        $this->addHeading1($section, 'ПРИЛОЖЕНИЕ 2. КЛАССИФИКАЦИЯ ТЕРМИНОВ');
        (new TermsClassificationAppendix())->build($section, $context);
        $section->addPageBreak();

        $this->addHeading1($section, 'ПРИЛОЖЕНИЕ 3. КЛАССИФИКАЦИЯ УСЛОВНЫХ ОБОЗНАЧЕНИЙ');
        (new SymbolsClassificationAppendix())->build($section, $context);
        $section->addPageBreak();

        $this->addHeading1($section, 'ПРИЛОЖЕНИЕ 4. ПРОГРАММА ПРОВЕДЕНИЯ ОБСЛЕДОВАНИЯ');
        (new InspectionProgramAppendix())->build($section, $context);
        $section->addPageBreak();

        $this->addHeading1($section, 'ПРИЛОЖЕНИЕ 5. СЕРТИФИКАТЫ');
        (new CertificatesAppendix())->build($section, $context);
        $section->addPageBreak();

        $this->addHeading1($section, 'ПРИЛОЖЕНИЕ 6. ВЫПИСКА ИЗ РЕЕСТРА ЧЛЕНОВ СРО');
        (new SroExcerptAppendix())->build($section, $context);
        $section->addPageBreak();

        $this->addHeading1($section, 'ПРИЛОЖЕНИЕ 7. УВЕДОМЛЕНИЕ НОПРИЗ');
        (new NoprizNotificationAppendix())->build($section, $context);
        $section->addPageBreak();

        $this->addHeading1($section, 'ПРИЛОЖЕНИЕ 8. СПИСОК ОБОРУДОВАНИЯ');
        (new EquipmentListAppendix())->build($section, $context);

        // ── Сохранение ────────────────────────────────────────────────────────
        if (!is_dir($outputDir) && !mkdir($outputDir, 0755, true)) {
            throw new \RuntimeException(sprintf('Не удалось создать директорию "%s"', $outputDir));
        }

        $filePath = sprintf('%s/ots_report_%d.docx', rtrim($outputDir, '/'), $calculationId);
        IOFactory::createWriter($phpWord, 'Word2007')->save($filePath);

        return $filePath;
    }

    private function createDocument(): PhpWord
    {
        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Times New Roman');
        $phpWord->setDefaultFontSize(10);

        // Стили уровней заголовков для автоматического содержания
        $phpWord->addTitleStyle(1, [
            'bold'  => true,
            'size'  => 12,
            'name'  => 'Times New Roman',
        ], [
            'alignment'   => Jc::CENTER,
            'spaceBefore' => Converter::cmToTwip(0.3),
            'spaceAfter'  => Converter::cmToTwip(0.3),
        ]);

        $phpWord->addTitleStyle(2, [
            'bold'  => true,
            'size'  => 11,
            'name'  => 'Times New Roman',
        ], [
            'alignment'   => Jc::START,
            'spaceBefore' => Converter::cmToTwip(0.2),
            'spaceAfter'  => Converter::cmToTwip(0.2),
        ]);

        // Единственная секция A4 с полями по ГОСТ
        $phpWord->addSection([
            'paperSize'    => 'A4',
            'marginLeft'   => Converter::cmToTwip(3.0),
            'marginRight'  => Converter::cmToTwip(1.5),
            'marginTop'    => Converter::cmToTwip(2.0),
            'marginBottom' => Converter::cmToTwip(2.0),
        ]);

        return $phpWord;
    }

    private function addHeading1(\PhpOffice\PhpWord\Element\Section $section, string $text): void
    {
        $section->addTitle($text, 1);
    }
}
