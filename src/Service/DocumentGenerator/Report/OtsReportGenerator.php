<?php

declare(strict_types=1);

namespace App\Service\DocumentGenerator\Report;

use App\Entity\CalculationImage;
use App\Exception\NotFoundException;
use App\Service\DocumentGenerator\Report\Section\Appendix\CertificatesAppendix;
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
        private ReportContextFactory $contextFactory,
        private ReportDocumentBuilder $documentBuilder,
        private WindLoadsSection $windLoadsSection,
        private CertificatesAppendix $certificatesAppendix,
        private SroExcerptAppendix $sroExcerptAppendix,
        private NoprizNotificationAppendix $noprizNotificationAppendix,
        private string $projectDir,
    ) {
    }

    /**
     * @throws NotFoundException     если расчёт не найден
     * @throws \RuntimeException     если не удалось создать директорию или сохранить файл
     */
    public function generate(int $calculationId, string $outputDir): string
    {
        $context = $this->contextFactory->create($calculationId);

        $phpWord = $this->documentBuilder->createDocument();
        $sectionTitle = $phpWord->getSection(ReportDocumentBuilder::TITLE_SECTION_INDEX);

        // ── Титульные листы ───────────────────────────────────────────────────
        (new TitlePageGenerator($this->projectDir))->build($sectionTitle, $context);

        $mainSection = $phpWord->getSection(ReportDocumentBuilder::MAIN_SECTION_INDEX);
        $this->documentBuilder->addFooterStamp($mainSection, $context);
        $this->documentBuilder->addTableOfContents($mainSection);

        $sectionNum = 0;
        $appendixNum = 0;
        $tableNum = 0;

        // ── Разделы ───────────────────────────────────────────────────────────
        $this->documentBuilder->addSection($mainSection, $sectionNum, 'ОБЩИЕ ДАННЫЕ');
        (new GeneralDataSection())->build($mainSection, $context, $tableNum);

        $this->documentBuilder->addSection($mainSection, $sectionNum, 'ЦЕЛЬ ПРОВЕДЕНИЯ РАСЧЁТА И ОБСЛЕДОВАНИЯ');
        (new PurposeSection())->build($mainSection, $context, $tableNum);
        $mainSection->addPageBreak();

        $this->documentBuilder->addSection($mainSection, $sectionNum, 'ПРЕДОСТАВЛЕННАЯ ДОКУМЕНТАЦИЯ');
        (new DocumentationSection())->build($mainSection, $context, $tableNum);
        $mainSection->addPageBreak();

        $this->documentBuilder->addSection($mainSection, $sectionNum, 'ГЕОГРАФИЧЕСКИЕ ПАРАМЕТРЫ И КЛИМАТИЧЕСКИЕ УСЛОВИЯ РАСПОЛОЖЕНИЯ СООРУЖЕНИЯ');
        (new ClimateSection())->build($mainSection, $context, $tableNum);

        $this->documentBuilder->addSection($mainSection, $sectionNum, 'ХАРАКТЕРИСТИКИ МАТЕРИАЛА КОНСТРУКЦИЙ');
        (new MaterialSection())->build($mainSection, $context, $tableNum);
        $mainSection->addPageBreak();

        $this->documentBuilder->addSection($mainSection, $sectionNum, 'КОНСТРУКТИВНОЕ РЕШЕНИЕ СООРУЖЕНИЯ');
        (new StructuralSection())->build($mainSection, $context, $tableNum);
        $mainSection->addPageBreak();

        $this->documentBuilder->addSection($mainSection, $sectionNum, 'СХЕМА ОПОРЫ');
        (new PillarSchemeSection())->build($mainSection, $context, $tableNum);
        $mainSection->addPageBreak();

        $this->documentBuilder->addSection($mainSection, $sectionNum, 'ГОРИЗОНТАЛЬНЫЕ НАГРУЗКИ');
        $this->windLoadsSection->build($mainSection, $context, $tableNum);
        $mainSection->addPageBreak();

        $this->documentBuilder->addSection($mainSection, $sectionNum, 'ВЕРТИКАЛЬНЫЕ НАГРУЗКИ');
        (new VerticalLoadsSection())->build($mainSection, $context, $tableNum);
        $mainSection->addPageBreak();

        $this->documentBuilder->addSection($mainSection, $sectionNum, 'ОСНОВНЫЕ РАСЧЁТНЫЕ ПОЛОЖЕНИЯ');
        (new CalculationBasisSection())->build($mainSection, $context, $tableNum);
        $mainSection->addPageBreak();

        $this->documentBuilder->addSection($mainSection, $sectionNum, 'ПРОГРАММНЫЙ РАСЧЁТ ОПОРЫ');
        (new ProgramCalculationSection($sectionNum))->build($mainSection, $context, $tableNum);
        $mainSection->addPageBreak();

        $this->documentBuilder->addSection($mainSection, $sectionNum, 'РЕЗУЛЬТАТЫ РАСЧЁТА И ВЫВОДЫ');
        (new CalculationResultsSection())->build($mainSection, $context, $tableNum);
        $mainSection->addPageBreak();

        $this->documentBuilder->addSection($mainSection, $sectionNum, 'ЗАКЛЮЧЕНИЕ');
        (new ConclusionSection())->build($mainSection, $context, $tableNum);
        $mainSection->addPageBreak();

        // ── Приложения ────────────────────────────────────────────────────────
        $this->documentBuilder->addAppendix($mainSection, $appendixNum, 'ВЕДОМОСТЬ ССЫЛОЧНЫХ ДОКУМЕНТОВ');
        (new ReferenceDocumentsAppendix())->build($mainSection, $context, $tableNum);
        $mainSection->addPageBreak();

        $this->documentBuilder->addAppendix($mainSection, $appendixNum, 'КЛАССИФИКАЦИЯ ТЕРМИНОВ');
        (new TermsClassificationAppendix())->build($mainSection, $context, $tableNum);
        $mainSection->addPageBreak();

        $this->documentBuilder->addAppendix($mainSection, $appendixNum, 'КЛАССИФИКАЦИЯ УСЛОВНЫХ ОБОЗНАЧЕНИЙ');
        (new SymbolsClassificationAppendix())->build($mainSection, $context, $tableNum);
        $mainSection->addPageBreak();

        $this->documentBuilder->addAppendix(
            $mainSection,
            $appendixNum,
            $context->getData()?->isSurveyPerformed()
                ? 'ПРОГРАММА ПРОВЕДЕНИЯ ОБСЛЕДОВАНИЯ И РАСЧЁТА'
                : 'ПРОГРАММА ПРОВЕДЕНИЯ РАСЧЁТА',
        );
        (new InspectionProgramAppendix())->build($mainSection, $context, $tableNum);
        $mainSection->addPageBreak();

        $this->documentBuilder->addAppendix($mainSection, $appendixNum, 'СЕРТИФИКАТЫ');
        $this->certificatesAppendix->build($mainSection, $context, $tableNum);
        $mainSection->addPageBreak();

        $this->documentBuilder->addAppendix($mainSection, $appendixNum, 'ВЫПИСКА ИЗ РЕЕСТРА ЧЛЕНОВ СРО');
        $this->sroExcerptAppendix->build($mainSection, $context, $tableNum);
        $mainSection->addPageBreak();

        $this->documentBuilder->addAppendix($mainSection, $appendixNum, 'УВЕДОМЛЕНИЕ НОПРИЗ');
        $this->noprizNotificationAppendix->build($mainSection, $context, $tableNum);

        if ($context->getCalculationImagesByType(CalculationImage::TYPE_EQUIPMENT_LIST) !== []) {
            $mainSection->addPageBreak();
            $this->documentBuilder->addAppendix($mainSection, $appendixNum, 'ПЕРЕЧЕНЬ ОБОРУДОВАНИЯ НА ОПОРЕ');
            (new EquipmentOnPillarAppendix())->build($mainSection, $context, $tableNum);
        }

        if ($context->getCalculationImagesByType(CalculationImage::TYPE_FOUNDATION_CALC) !== []) {
            $mainSection->addPageBreak();
            $this->documentBuilder->addAppendix($mainSection, $appendixNum, 'РАСЧЁТ ФУНДАМЕНТА ОПОРЫ');
            (new FoundationCalcAppendix())->build($mainSection, $context, $tableNum);
        }

        return $this->documentBuilder->save($phpWord, $outputDir, sprintf('ots_report_%d.docx', $calculationId));
    }
}
