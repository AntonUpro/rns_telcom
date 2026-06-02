<?php

declare(strict_types=1);

namespace App\Service\DocumentGenerator\Report;

use App\Exception\NotFoundException;
use App\Repository\AppendixStaticImageRepository;
use App\Repository\CalculationDocumentRepository;
use App\Repository\CalculationImageRepository;
use App\Repository\CalculationRepository;
use App\Repository\CalculationReportFileRepository;
use App\Repository\CalculationResultTableRepository;
use App\Service\DocumentGenerator\DocStyleRegistry;
use App\Entity\CalculationImage;
use App\Service\DocumentGenerator\Report\Section\Appendix\CertificatesAppendix;
use App\Service\DocumentGenerator\Report\Section\Appendix\EquipmentListAppendix;
use App\Service\DocumentGenerator\Report\Section\Appendix\EquipmentOnPillarAppendix;
use App\Service\DocumentGenerator\Report\Section\Appendix\FoundationCalcAppendix;
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
use App\Service\DocumentGenerator\Report\Section\TitlePageGenerator;
use App\Service\DocumentGenerator\Report\Section\VerticalLoadsSection;
use App\Service\DocumentGenerator\Report\Section\WindLoadsSection;
use PhpOffice\PhpWord\SimpleType\TblWidth;
use PhpOffice\PhpWord\ComplexType\TblWidth as TblWidthType;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Shared\Converter;
use PhpOffice\PhpWord\SimpleType\Jc;
use PhpOffice\PhpWord\Element\Section;
use PhpOffice\PhpWord\Style\Language;
use RuntimeException;
use ZipArchive;

/**
 * Генерирует полный отчёт ОТС (обследование технического состояния) в формате DOCX.
 *
 * Структура документа:
 *   Титульный лист 1 (обложка с реквизитами ООО «ТелКом»)
 *   Титульный лист 2 (шифр, заказчик, адрес объекта)
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
        private CalculationRepository $calculationRepository,
        private CalculationDocumentRepository $documentRepository,
        private CalculationImageRepository $imageRepository,
        private CalculationResultTableRepository $resultTableRepository,
        private CalculationReportFileRepository $reportFileRepository,
        private WindLoadsSection $windLoadsSection,
        private CertificatesAppendix $certificatesAppendix,
        private SroExcerptAppendix $sroExcerptAppendix,
        private NoprizNotificationAppendix $noprizNotificationAppendix,
        private AppendixStaticImageRepository $appendixImageRepository,
        private string $projectDir,
    ) {
    }

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
            calculation: $calculation,
            documents: $this->documentRepository->findByCalculation($calculationId),
            resultTables: $this->resultTableRepository->findAllByCalculationIndexed($calculation),
            calculationImages: $this->imageRepository->findByCalculation($calculationId),
            appendixImages: $this->appendixImageRepository->findAllGroupedByType(),
        );

        $phpWord = $this->createDocument();
        $sectionTitle = $phpWord->getSection(0);

        // ── Титульные листы ───────────────────────────────────────────────────
        (new TitlePageGenerator($this->projectDir))->build($sectionTitle, $context);

        $mainSection = $phpWord->getSection(1);
        $this->addFooterStamp($mainSection, $context);
        // ── Содержание ────────────────────────────────────────────────────────
        $mainSection->addTitle('СОДЕРЖАНИЕ', 1);
        $mainSection->addTOC(DocStyleRegistry::sectionTitle(), [], 1, 2);
        $mainSection->addPageBreak();

        $sectionNum = 0;
        $appendixNum = 0;

        // ── Разделы ───────────────────────────────────────────────────────────
        $this->addSection($mainSection, $sectionNum, 'ОБЩИЕ ДАННЫЕ');
        (new GeneralDataSection())->build($mainSection, $context);

        $this->addSection($mainSection, $sectionNum, 'ЦЕЛЬ ПРОВЕДЕНИЯ РАСЧЁТА И ОБСЛЕДОВАНИЯ');
        (new PurposeSection())->build($mainSection, $context);
        $mainSection->addPageBreak();

        $this->addSection($mainSection, $sectionNum, 'ПРЕДОСТАВЛЕННАЯ ДОКУМЕНТАЦИЯ');
        (new DocumentationSection())->build($mainSection, $context);
        $mainSection->addPageBreak();

        $this->addSection($mainSection, $sectionNum, 'ГЕОГРАФИЧЕСКИЕ ПАРАМЕТРЫ И КЛИМАТИЧЕСКИЕ УСЛОВИЯ РАСПОЛОЖЕНИЯ СООРУЖЕНИЯ');
        (new ClimateSection())->build($mainSection, $context);

        $this->addSection($mainSection, $sectionNum, 'ХАРАКТЕРИСТИКИ МАТЕРИАЛА КОНСТРУКЦИЙ');
        (new MaterialSection())->build($mainSection, $context);
        $mainSection->addPageBreak();

        $this->addSection($mainSection, $sectionNum, 'КОНСТРУКТИВНОЕ РЕШЕНИЕ СООРУЖЕНИЯ');
        (new StructuralSection())->build($mainSection, $context);
        $mainSection->addPageBreak();

        $this->addSection($mainSection, $sectionNum, 'СХЕМА ОПОРЫ');
        (new PillarSchemeSection())->build($mainSection, $context);
        $mainSection->addPageBreak();

        $this->addSection($mainSection, $sectionNum, 'ГОРИЗОНТАЛЬНЫЕ НАГРУЗКИ');
        $this->windLoadsSection->build($mainSection, $context);
        $mainSection->addPageBreak();

        $this->addSection($mainSection, $sectionNum, 'ВЕРТИКАЛЬНЫЕ НАГРУЗКИ');
        (new VerticalLoadsSection())->build($mainSection, $context);
        $mainSection->addPageBreak();

        $this->addSection($mainSection, $sectionNum, 'ОСНОВНЫЕ РАСЧЁТНЫЕ ПОЛОЖЕНИЯ');
        (new CalculationBasisSection())->build($mainSection, $context);
        $mainSection->addPageBreak();

        $this->addSection($mainSection, $sectionNum, 'ПРОГРАММНЫЙ РАСЧЁТ ОПОРЫ');
        (new ProgramCalculationSection())->build($mainSection, $context);
        $mainSection->addPageBreak();

        $this->addSection($mainSection, $sectionNum, 'РЕЗУЛЬТАТЫ РАСЧЁТА И ВЫВОДЫ');
        (new CalculationResultsSection())->build($mainSection, $context);
        $mainSection->addPageBreak();

        $this->addSection($mainSection, $sectionNum, 'ЗАКЛЮЧЕНИЕ');
        (new ConclusionSection())->build($mainSection, $context);
        $mainSection->addPageBreak();

        // ── Приложения ────────────────────────────────────────────────────────
        $this->addAppendix($mainSection, $appendixNum, 'ВЕДОМОСТЬ ССЫЛОЧНЫХ ДОКУМЕНТОВ');
        (new ReferenceDocumentsAppendix())->build($mainSection, $context);
        $mainSection->addPageBreak();

        $this->addAppendix($mainSection, $appendixNum, 'КЛАССИФИКАЦИЯ ТЕРМИНОВ');
        (new TermsClassificationAppendix())->build($mainSection, $context);
        $mainSection->addPageBreak();

        $this->addAppendix($mainSection, $appendixNum, 'КЛАССИФИКАЦИЯ УСЛОВНЫХ ОБОЗНАЧЕНИЙ');
        (new SymbolsClassificationAppendix())->build($mainSection, $context);
        $mainSection->addPageBreak();

        $this->addAppendix($mainSection, $appendixNum, 'ПРОГРАММА ПРОВЕДЕНИЯ ОБСЛЕДОВАНИЯ');
        (new InspectionProgramAppendix())->build($mainSection, $context);
        $mainSection->addPageBreak();

        $this->addAppendix($mainSection, $appendixNum, 'СЕРТИФИКАТЫ');
        $this->certificatesAppendix->build($mainSection, $context);
        $mainSection->addPageBreak();

        $this->addAppendix($mainSection, $appendixNum, 'ВЫПИСКА ИЗ РЕЕСТРА ЧЛЕНОВ СРО');
        $this->sroExcerptAppendix->build($mainSection, $context);
        $mainSection->addPageBreak();

        $this->addAppendix($mainSection, $appendixNum, 'УВЕДОМЛЕНИЕ НОПРИЗ');
        $this->noprizNotificationAppendix->build($mainSection, $context);
        $mainSection->addPageBreak();

        $this->addAppendix($mainSection, $appendixNum, 'СПИСОК ОБОРУДОВАНИЯ');
        (new EquipmentListAppendix())->build($mainSection, $context);

        if ($context->getCalculationImagesByType(CalculationImage::TYPE_EQUIPMENT_LIST) !== []) {
            $mainSection->addPageBreak();
            $this->addAppendix($mainSection, $appendixNum, 'ПЕРЕЧЕНЬ ОБОРУДОВАНИЯ НА ОПОРЕ');
            (new EquipmentOnPillarAppendix())->build($mainSection, $context);
        }

        if ($context->getCalculationImagesByType(CalculationImage::TYPE_FOUNDATION_CALC) !== []) {
            $mainSection->addPageBreak();
            $this->addAppendix($mainSection, $appendixNum, 'РАСЧЁТ ФУНДАМЕНТА ОПОРЫ');
            (new FoundationCalcAppendix())->build($mainSection, $context);
        }

        // ── Сохранение ────────────────────────────────────────────────────────
        if (! is_dir($outputDir) && ! mkdir($outputDir, 0755, true)) {
            throw new \RuntimeException(sprintf('Не удалось создать директорию "%s"', $outputDir));
        }

        $filePath = sprintf('%s/ots_report_%d.docx', rtrim($outputDir, '/'), $calculationId);
        IOFactory::createWriter($phpWord, 'Word2007')->save($filePath);
        $this->injectPageBorders($filePath);

        return $filePath;
    }

    private function createDocument(): PhpWord
    {
        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Times New Roman');
        $phpWord->setDefaultFontSize(12);
        $phpWord->getSettings()->setThemeFontLang(new Language(Language::RU_RU));
        $phpWord->getSettings()->setUpdateFields(true);

        // Стили уровней заголовков для автоматического содержания
        $phpWord->addTitleStyle(1, [
            'bold' => true,
            'size' => 12,
            'name' => 'Times New Roman',
            'italic' => true,
        ], [
            'alignment' => Jc::CENTER,
            'spaceBefore' => Converter::cmToTwip(0.3),
            'spaceAfter' => Converter::cmToTwip(0.3),
        ]);

        $phpWord->addTitleStyle(2, [
            'bold' => true,
            'size' => 12,
            'name' => 'Times New Roman',
            'italic' => true,
        ], [
            'alignment' => Jc::BOTH,
            'indentFirstLine' => Converter::cmToTwip(1.25),
            'spaceAfter' => Converter::cmToTwip(0.2),
        ]);

        // Секция с титулами
        $phpWord->addSection([
            'paperSize' => 'A4',
            'marginLeft' => Converter::cmToTwip(3.0),
            'marginRight' => Converter::cmToTwip(1.5),
            'marginTop' => Converter::cmToTwip(2.0),
            'marginBottom' => Converter::cmToTwip(2.0),
            'footerHeight' => Converter::cmToTwip(0.5),
        ]);
        // Главная секция
        $section = $phpWord->addSection([
            'paperSize' => 'A4',
            'marginLeft' => Converter::cmToTwip(3.0),
            'marginRight' => Converter::cmToTwip(1.5),
            'marginTop' => Converter::cmToTwip(1.5),
            'marginBottom' => Converter::cmToTwip(2.0),
            'headerHeight' => 0,
            'footerHeight' => Converter::cmToTwip(0.5),
        ]);
        $section->addHeader();
        $phpWord->setDefaultParagraphStyle(['line-spacing' => 150]);

        return $phpWord;
    }

    private function injectPageBorders(string $docxPath): void
    {
        $zip = new ZipArchive();
        if ($zip->open($docxPath) !== true) {
            throw new RuntimeException(sprintf('Не удалось открыть файл "%s" для добавления рамки', $docxPath));
        }

        $xml = $zip->getFromName('word/document.xml');
        if ($xml === false) {
            $zip->close();
            throw new RuntimeException('Не удалось прочитать word/document.xml из архива');
        }

        // offsetFrom="page": space задаётся от края листа в пунктах (0.5cm≈14pt, 2cm≈57pt)
        $borders = '<w:pgBorders w:offsetFrom="page">'
            . '<w:top w:val="single" w:sz="6" w:space="14" w:color="000000"/>'
            . '<w:left w:val="single" w:sz="6" w:space="57" w:color="000000"/>'
            . '<w:bottom w:val="single" w:sz="6" w:space="14" w:color="000000"/>'
            . '<w:right w:val="single" w:sz="6" w:space="14" w:color="000000"/>'
            . '</w:pgBorders>';

        $xml = str_replace('</w:sectPr>', $borders . '</w:sectPr>', $xml);

        // Пометить TOC-поле как dirty, чтобы Word обновил номера страниц при открытии
        $xml = str_replace(
            '<w:fldChar w:fldCharType="begin"/>',
            '<w:fldChar w:fldCharType="begin" w:dirty="true"/>',
            $xml,
        );

        $zip->addFromString('word/document.xml', $xml);

        // Гарантируем наличие updateFields в settings.xml
        $settingsXml = $zip->getFromName('word/settings.xml');
        if ($settingsXml !== false && !str_contains($settingsXml, 'w:updateFields')) {
            $settingsXml = str_replace(
                '</w:settings>',
                '<w:updateFields w:val="true"/></w:settings>',
                $settingsXml,
            );
            $zip->addFromString('word/settings.xml', $settingsXml);
        }

        $zip->close();
    }

    private function addSection(Section $section, int &$number, string $title): void
    {
        $number++;
        $section->addTitle(sprintf('%d. %s', $number, $title), 1);
    }

    private function addAppendix(Section $section, int &$number, string $title): void
    {
        $number++;
        $section->addTitle(sprintf('ПРИЛОЖЕНИЕ %d. %s', $number, $title), 1);
    }

    private function addFooterStamp(Section $section, ReportContext $context): void
    {
        $footer = $section->addFooter();
        // Структура штампа для формата А4 (185×30 мм)
        $stamp = $footer->addTable([
            'width' => Converter::cmToTwip(185),
            'unit' => TblWidth::TWIP,
            'borderSize' => 10, // Внутренние линии тоньше
            'borderColor' => '000000',
            'cellMargin' => 0, // Отступ внутри ячеек
            'borderBottomSize' => 0,
            'indent' => new TblWidthType(Converter::cmToTwip(-1), TblWidth::TWIP),
            'alignment' => 'left'
        ]);

        $fStyle = [
            'size' => 8,
            'name' => 'Times New Roman',
            'italic' => true,
            'line' => 240,
            'lineRule' => 'auto',
            'lineHeight' => 1,
        ];
        $pStyle = [
            'alignment' => Jc::CENTER,
            'valign' => 'center',
            'gridSpan' => 3,
            'line' => 240,
            'lineRule' => 'auto',
            'lineHeight' => 1,
        ];

        // Строки штампа по ГОСТ 2.104-2006 (упрощённая структура)
        $stamp->addRow(Converter::cmToTwip(0.5));
        $stamp->addCell(Converter::cmToTwip(0.7))->addText('', $fStyle, $pStyle);
        $stamp->addCell(Converter::cmToTwip(1.0))->addText('', $fStyle, $pStyle);
        $stamp->addCell(Converter::cmToTwip(2.3))->addText('', $fStyle, $pStyle);
        $stamp->addCell(Converter::cmToTwip(1.5))->addText('', $fStyle, $pStyle);
        $stamp->addCell(Converter::cmToTwip(1.0))->addText('', $fStyle, $pStyle);
        $stamp->addCell(Converter::cmToTwip(11.0), ['vMerge' => 'restart', 'valign' => 'center'])->addText(
            $context->calculation?->getCalculationData()->getObjectCode(),
            array_merge($fStyle, ['size' => 12]),
            array_merge($pStyle, ['valign' => 'center']),
        );
        $stamp->addCell(Converter::cmToTwip(1.0), ['vMerge' => 'restart'])->addText('Лист', $fStyle, $pStyle);

        $stamp->addRow(Converter::cmToTwip(0.5));
        $stamp->addCell(Converter::cmToTwip(0.7))->addText('', $fStyle, $pStyle);
        $stamp->addCell(Converter::cmToTwip(1.0))->addText('', $fStyle, $pStyle);
        $stamp->addCell(Converter::cmToTwip(2.3))->addText('', $fStyle, $pStyle);
        $stamp->addCell(Converter::cmToTwip(1.5))->addText('', $fStyle, $pStyle);
        $stamp->addCell(Converter::cmToTwip(1.0))->addText('', $fStyle, $pStyle);
        $stamp->addCell(Converter::cmToTwip(11.0), ['vMerge' => 'continue']);
        $stamp->addCell(Converter::cmToTwip(1.0),  ['vMerge' => 'continue']);

        $stamp->addRow(Converter::cmToTwip(0.5));
        $stamp->addCell(Converter::cmToTwip(0.7))->addText('Изм', $fStyle, $pStyle);
        $stamp->addCell(Converter::cmToTwip(1.0))->addText('Кол.уч', $fStyle, $pStyle);
        $stamp->addCell(Converter::cmToTwip(2.3))->addText('№ док.', $fStyle, $pStyle);
        $stamp->addCell(Converter::cmToTwip(1.5))->addText('Подпись', $fStyle, $pStyle);
        $stamp->addCell(Converter::cmToTwip(1.0))->addText('Дата', $fStyle, $pStyle);
        $stamp->addCell(Converter::cmToTwip(11.0), ['vMerge' => 'continue']);
        $c = $stamp->addCell(Converter::cmToTwip(1.0));
        $c->addPreserveText('{PAGE}', ['size' => 10, 'name' => 'Times New Roman', 'italic' => true], $pStyle);
    }
}
