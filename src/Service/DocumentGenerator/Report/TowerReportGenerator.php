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
use App\Service\DocumentGenerator\Report\Section\Appendix\TermsClassificationAppendix;
use App\Service\DocumentGenerator\Report\Section\Appendix\TowerSymbolsClassificationAppendix;
use App\Service\DocumentGenerator\Report\Section\ClimateSection;
use App\Service\DocumentGenerator\Report\Section\DocumentationSection;
use App\Service\DocumentGenerator\Report\Section\GeneralDataSection;
use App\Service\DocumentGenerator\Report\Section\PillarSchemeSection;
use App\Service\DocumentGenerator\Report\Section\PurposeSection;
use App\Service\DocumentGenerator\Report\Section\TitlePageGenerator;
use App\Service\DocumentGenerator\Report\Section\Tower\TowerCalculationBasisSection;
use App\Service\DocumentGenerator\Report\Section\Tower\TowerCalculationResultsSection;
use App\Service\DocumentGenerator\Report\Section\Tower\TowerConclusionSection;
use App\Service\DocumentGenerator\Report\Section\Tower\TowerMaterialSection;
use App\Service\DocumentGenerator\Report\Section\Tower\TowerProgramCalculationSection;
use App\Service\DocumentGenerator\Report\Section\Tower\TowerStructuralSection;
use App\Service\DocumentGenerator\Report\Section\Tower\TowerVerticalLoadsSection;
use App\Service\DocumentGenerator\Report\Section\Tower\TowerWindLoadsSection;

/**
 * Генерирует отчёт ОТС для башни в формате DOCX.
 * Оформление (титул, штамп, рамка, стили) общее с отчётом столба — {@see ReportDocumentBuilder}.
 *
 * Структура документа:
 *   Титульные листы, содержание
 *   1. Общие данные
 *   2. Цель проведения расчёта
 *   3. Предоставленная документация
 *   4. Географические параметры и климатические условия
 *   5. Характеристики материала конструкций
 *   6. Конструктивное решение сооружения
 *   7. Схема опоры
 *   8. Горизонтальные нагрузки (8.1 каркас башни, 8.2 оборудование и коммуникации)
 *   9. Вертикальные нагрузки (9.1 собственный вес, 9.2 гололёд)
 *   10. Основные расчётные положения
 *   11. Программный расчёт опоры
 *   12. Результаты расчёта и выводы
 *   13. Заключение
 *   Приложения 1–7 и, при наличии изображений, «Перечень оборудования» и «Расчёт фундамента»
 */
final readonly class TowerReportGenerator
{
    /** Количество обязательных приложений (до необязательных) */
    private const REQUIRED_APPENDIX_COUNT = 7;

    public function __construct(
        private ReportContextFactory $contextFactory,
        private ReportDocumentBuilder $documentBuilder,
        private TowerWindLoadsSection $windLoadsSection,
        private TowerVerticalLoadsSection $verticalLoadsSection,
        private CertificatesAppendix $certificatesAppendix,
        private SroExcerptAppendix $sroExcerptAppendix,
        private NoprizNotificationAppendix $noprizNotificationAppendix,
        private string $projectDir,
    ) {
    }

    /**
     * @throws NotFoundException  если расчёт не найден
     * @throws \RuntimeException  если не удалось создать директорию или сохранить файл
     */
    public function generate(int $calculationId, string $outputDir): string
    {
        $context = $this->contextFactory->create($calculationId);

        $hasEquipmentList = $context->getCalculationImagesByType(CalculationImage::TYPE_EQUIPMENT_LIST) !== [];
        $hasFoundationCalc = $context->getCalculationImagesByType(CalculationImage::TYPE_FOUNDATION_CALC) !== [];
        $foundationAppendixNum = $hasFoundationCalc
            ? self::REQUIRED_APPENDIX_COUNT + ($hasEquipmentList ? 1 : 0) + 1
            : null;

        $phpWord = $this->documentBuilder->createDocument();
        $builder = $this->documentBuilder;

        // ── Титульные листы ───────────────────────────────────────────────────
        (new TitlePageGenerator($this->projectDir, sprintf('Башня, Н=%s м', $context->getHeightM())))
            ->build($phpWord->getSection(ReportDocumentBuilder::TITLE_SECTION_INDEX), $context);

        $main = $phpWord->getSection(ReportDocumentBuilder::MAIN_SECTION_INDEX);
        $builder->addFooterStamp($main, $context);
        $builder->addTableOfContents($main);

        $sectionNum = 0;
        $appendixNum = 0;
        $tableNum = 0;

        // ── Разделы ───────────────────────────────────────────────────────────
        $builder->addSection($main, $sectionNum, 'ОБЩИЕ ДАННЫЕ');
        (new GeneralDataSection())->build($main, $context, $tableNum);

        $builder->addSection(
            $main,
            $sectionNum,
            $context->getData()?->isSurveyPerformed()
                ? 'ЦЕЛЬ ПРОВЕДЕНИЯ РАСЧЁТА И ОБСЛЕДОВАНИЯ'
                : 'ЦЕЛЬ ПРОВЕДЕНИЯ РАСЧЁТА',
        );
        (new PurposeSection())->build($main, $context, $tableNum);
        $main->addPageBreak();

        $builder->addSection($main, $sectionNum, 'ПРЕДОСТАВЛЕННАЯ ДОКУМЕНТАЦИЯ');
        (new DocumentationSection())->build($main, $context, $tableNum);
        $main->addPageBreak();

        $builder->addSection($main, $sectionNum, 'ГЕОГРАФИЧЕСКИЕ ПАРАМЕТРЫ И КЛИМАТИЧЕСКИЕ УСЛОВИЯ РАСПОЛОЖЕНИЯ СООРУЖЕНИЯ');
        (new ClimateSection(withCoordinates: true, withIceAndSnow: true))->build($main, $context, $tableNum);

        $builder->addSection($main, $sectionNum, 'ХАРАКТЕРИСТИКИ МАТЕРИАЛА КОНСТРУКЦИЙ');
        (new TowerMaterialSection())->build($main, $context, $tableNum);
        $main->addPageBreak();

        $builder->addSection($main, $sectionNum, 'КОНСТРУКТИВНОЕ РЕШЕНИЕ СООРУЖЕНИЯ');
        (new TowerStructuralSection())->build($main, $context, $tableNum);
        $main->addPageBreak();

        $builder->addSection($main, $sectionNum, 'СХЕМА ОПОРЫ');
        (new PillarSchemeSection())->build($main, $context, $tableNum);
        $main->addPageBreak();

        $builder->addSection($main, $sectionNum, 'ГОРИЗОНТАЛЬНЫЕ НАГРУЗКИ');
        $this->windLoadsSection->build($main, $context, $tableNum);
        $main->addPageBreak();

        $builder->addSection($main, $sectionNum, 'ВЕРТИКАЛЬНЫЕ НАГРУЗКИ');
        $this->verticalLoadsSection->build($main, $context, $tableNum);
        $main->addPageBreak();

        $builder->addSection($main, $sectionNum, 'ОСНОВНЫЕ РАСЧЁТНЫЕ ПОЛОЖЕНИЯ');
        (new TowerCalculationBasisSection())->build($main, $context, $tableNum);
        $main->addPageBreak();

        $builder->addSection($main, $sectionNum, 'ПРОГРАММНЫЙ РАСЧЁТ ОПОРЫ');
        (new TowerProgramCalculationSection())->build($main, $context, $tableNum);
        $main->addPageBreak();

        $builder->addSection($main, $sectionNum, 'РЕЗУЛЬТАТЫ РАСЧЁТА И ВЫВОДЫ');
        (new TowerCalculationResultsSection($foundationAppendixNum))->build($main, $context, $tableNum);
        $main->addPageBreak();

        $builder->addSection($main, $sectionNum, 'ЗАКЛЮЧЕНИЕ');
        (new TowerConclusionSection())->build($main, $context, $tableNum);
        $main->addPageBreak();

        // ── Приложения ────────────────────────────────────────────────────────
        $builder->addAppendix($main, $appendixNum, 'ВЕДОМОСТЬ ССЫЛОЧНЫХ ДОКУМЕНТОВ');
        (new ReferenceDocumentsAppendix())->build($main, $context, $tableNum);
        $main->addPageBreak();

        $builder->addAppendix($main, $appendixNum, 'КЛАССИФИКАЦИЯ ТЕРМИНОВ');
        (new TermsClassificationAppendix())->build($main, $context, $tableNum);
        $main->addPageBreak();

        $builder->addAppendix($main, $appendixNum, 'КЛАССИФИКАЦИЯ УСЛОВНЫХ ОБОЗНАЧЕНИЙ');
        (new TowerSymbolsClassificationAppendix())->build($main, $context, $tableNum);
        $main->addPageBreak();

        $builder->addAppendix(
            $main,
            $appendixNum,
            $context->getData()?->isSurveyPerformed()
                ? 'ПРОГРАММА ПРОВЕДЕНИЯ ОБСЛЕДОВАНИЯ И РАСЧЁТА'
                : 'ПРОГРАММА ПРОВЕДЕНИЯ РАСЧЁТА',
        );
        (new InspectionProgramAppendix())->build($main, $context, $tableNum);
        $main->addPageBreak();

        $builder->addAppendix($main, $appendixNum, 'СЕРТИФИКАТЫ');
        $this->certificatesAppendix->build($main, $context, $tableNum);
        $main->addPageBreak();

        $builder->addAppendix($main, $appendixNum, 'ВЫПИСКА ИЗ РЕЕСТРА ЧЛЕНОВ СРО');
        $this->sroExcerptAppendix->build($main, $context, $tableNum);
        $main->addPageBreak();

        $builder->addAppendix($main, $appendixNum, 'УВЕДОМЛЕНИЕ НОПРИЗ');
        $this->noprizNotificationAppendix->build($main, $context, $tableNum);

        if ($hasEquipmentList) {
            $main->addPageBreak();
            $builder->addAppendix($main, $appendixNum, 'ПЕРЕЧЕНЬ ОБОРУДОВАНИЯ НА ОПОРЕ');
            (new EquipmentOnPillarAppendix())->build($main, $context, $tableNum);
        }

        if ($hasFoundationCalc) {
            $main->addPageBreak();
            $builder->addAppendix($main, $appendixNum, 'РАСЧЁТ ФУНДАМЕНТА');
            (new FoundationCalcAppendix())->build($main, $context, $tableNum);
        }

        return $builder->save($phpWord, $outputDir, sprintf('ots_tower_report_%d.docx', $calculationId));
    }
}
