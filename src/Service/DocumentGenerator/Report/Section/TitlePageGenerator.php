<?php

declare(strict_types=1);

namespace App\Service\DocumentGenerator\Report\Section;

use App\Service\DocumentGenerator\Report\ReportContext;
use PhpOffice\PhpWord\ComplexType\TblWidth as TblWidthType;
use PhpOffice\PhpWord\Element\Section;
use PhpOffice\PhpWord\Shared\Converter;
use PhpOffice\PhpWord\SimpleType\Jc;
use PhpOffice\PhpWord\SimpleType\TblWidth;

/**
 * Генерирует два титульных листа перед содержанием:
 *   Лист 1 — обложка с реквизитами ООО «ТелКом»
 *   Лист 2 — титул с шифром, заказчиком и адресом объекта
 */
final class TitlePageGenerator
{
    /** Реквизиты компании — статичные данные из шаблона */
    private const COMPANY_NAME = 'Общество с ограниченной ответственностью «ТелКом»';
    private const ACTUAL_ADDRESS = '620075, г. Екатеринбург, ул. Первомайская, 56, офис 706';
    private const LEGAL_ADDRESS = '614068, г. Пермь, ул. Сухобруса, д.27, оф. 405';
    private const INN = '5903099085';
    private const KPP = '590301001';
    private const OGRN = '1095903005604';
    private const PHONE = '+7 (932) 611-53-00';
    private const EMAIL = 'info@telkom.spb.ru';
    private const SITE = 'www.телком.рф';

    public function __construct(
        private readonly string $projectDir,
    ) {
    }

    public function build(Section $section, ReportContext $context): void
    {
        $this->buildHeader($section);
        $this->buildFooter($section);
        $this->buildCoverPage($section, $context);
        $section->addPageBreak();
        $this->buildTitlePage($section, $context);
    }

    private function buildHeader(Section $section): void
    {
        $header = $section->addHeader();

        // Экз №___
        $header->addText('Экз №___', [
            'size' => 12,
            'name' => 'Arial',
            'italic' => true,
        ], [
            'alignment' => Jc::END,
            'indentation' => [
                'right' => (int)Converter::cmToTwip(1),
            ],
            'spaceAfter' => 0,
        ]);

        $header->addImage($this->projectDir . '/static_image/telcom_title.png', [
            'width' => Converter::cmToPoint(17),
            'height' => Converter::cmToPoint(3),
            'wrappingStyle' => 'inline',
            'alignment' => Jc::CENTER,
        ]);

        $companyInformationFStyle = [
            'size' => 8,
            'name' => 'Arial',
            'italic' => false,
        ];
        $companyInformationPStyle = [
            'alignment' => Jc::CENTER,
            'spaceAfter' => 0,
            'spaceBefore' => 0,
            'lineHeight' => 1,
            'line' => 240,
            'lineRule' => 'auto'
        ];

        // Название компании — крупный центральный заголовок
        $header->addText(self::COMPANY_NAME, $companyInformationFStyle, $companyInformationPStyle);
        // Фактический адрес
        $header->addText('Фактический адрес: ' . self::ACTUAL_ADDRESS, $companyInformationFStyle, $companyInformationPStyle);
        // Юридический адрес
        $header->addText('Юридический адрес: ' . self::LEGAL_ADDRESS, $companyInformationFStyle, $companyInformationPStyle);
        // ИНН, КПП, ОГРН
        $header->addText(
            sprintf('ИНН %s, КПП %s, ОГРН %s Тел/факс: %s', self::INN, self::KPP, self::OGRN, self::PHONE),
            $companyInformationFStyle,
            $companyInformationPStyle,
        );
        // Email Сайт
        $header->addText(sprintf('e-mail: %s, сайт: %s', self::EMAIL, self::SITE), $companyInformationFStyle, $companyInformationPStyle);

        $header->addTextBreak();
    }

    private function buildFooter(Section $section): void
    {
        $footer = $section->addFooter();
        $footer->addText((new \DateTime())->format('Y'), [
            'size' => 12,
            'name' => 'Times New Roman',
            'italic' => true,
        ], [
            'alignment' => Jc::CENTER,
            'line' => 240,
            'lineRule' => 'auto'
        ]);
    }

    /**
     * Лист 1 — обложка с реквизитами компании.
     */
    private function buildCoverPage(Section $section, ReportContext $context): void
    {
        // Пустое пространство до названия документа
        $section->addTextBreak(1);

        $fStyle = [
            'size' => 12,
            'name' => 'Times New Roman',
            'italic' => true,
        ];
        $pStyle = [
            'alignment' => Jc::CENTER,
            'spaceAfter' => 0,
            'line' => 276,
            'lineRule' => 'exact',
            'lineHeight' => 1.15,
        ];

        // Заказчик
        $section->addText(sprintf("Заказчик: %s", $context->calculation?->getCalculationData()?->getCustomer()?->getOtsName()), $fStyle, $pStyle);
        // Адрес
        $section->addText(
            sprintf('%s, %s', $context->calculation?->getCalculationData()?->getRegion(), $context->calculation?->getCalculationData()?->getLocality()),
            $fStyle,
            $pStyle,
        );

        $section->addTextBreak(2);

        $this->addBlockTechnicalReport($section, $context);

        $section->addTextBreak(2);

        $section->addText('Шифр проекта: ' . $context->calculation->getCalculationData()->getObjectCode(), [
            'size' => 14,
            'name' => 'Times New Roman',
            'italic' => true,
        ], [
            'alignment' => Jc::CENTER,
            'line' => 240,
            'lineRule' => 'auto',
            'lineHeight' => 1,
        ]);
    }

    /**
     * Лист 2 — титул с шифром, заказчиком и адресом объекта.
     */
    private function buildTitlePage(Section $section, ReportContext $context): void
    {
        // Пустые строки для вертикального позиционирования
        $section->addTextBreak(3);

        $colWidth = Converter::cmToTwip(8.75);

        // Стиль ячейки с черными границами
        $cellStyle = [
            'borderTopSize' => 6,
            'borderTopColor' => '000000',
            'borderLeftSize' => 6,
            'borderLeftColor' => '000000',
            'borderRightSize' => 6,
            'borderRightColor' => '000000',
            'borderBottomSize' => 6,
            'borderBottomColor' => '000000',
            'valign' => 'center',
            'marginLeft' => 100,
        ];

        $fStyle = [
            'size' => 16,
            'name' => 'Times New Roman',
            'italic' => true,
            'line' => 240,
            'lineRule' => 'auto',
            'lineHeight' => 1,
        ];

        $table = $section->addTable([
            'indent' => new TblWidthType(Converter::cmToTwip(1), TblWidth::TWIP),
        ]);

        // --- Строка 1 ---
        $table->addRow(Converter::cmToTwip(1));
        $c1 = $table->addCell($colWidth, $cellStyle);
        $c1->addText('Субъект РФ', $fStyle);
        $c2 = $table->addCell($colWidth, $cellStyle);
        $c2->addText($context->calculation?->getCalculationData()?->getRegion(), $fStyle);

        // --- Строка 2 ---
        $table->addRow(Converter::cmToTwip(1));
        $c1 = $table->addCell($colWidth, $cellStyle);
        $c1->addText('Адрес площадки', $fStyle);
        $c2 = $table->addCell($colWidth, $cellStyle);
        $c2->addText($context->calculation?->getCalculationData()?->getLocality(), $fStyle);

        // --- Строка 3 ---
        $table->addRow(Converter::cmToTwip(1));
        $c1 = $table->addCell($colWidth, $cellStyle);
        $c1->addText('Номер и название БС по классификации оператора сотовой связи', $fStyle);
        $c2 = $table->addCell($colWidth, $cellStyle);
        $c2->addText($context->calculation?->getCalculationData()?->getObjectCode(), $fStyle);

        $heightAMS = $context->calculation?->getCalculationData()?->getAmsHeight() ?? '—';
        $heightPillar = $context->calculation?->getCalculationData()?->getConcretePillarSpecificData()?->pillarHeight ?? '—';
        $textPillar = 'Столб ж/б';
        if ((int)$heightAMS > (int)$heightPillar) {
            $textPillar .= 'с металлической надстройкой';
        }
        $textPillar .= sprintf(', Н=%s м', $heightAMS);

        // --- Строка 4 (две высоты в одной ячейке) ---
        $table->addRow(Converter::cmToTwip(1));
        $c1 = $table->addCell($colWidth, $cellStyle);
        $c1->addText('Тип АМС и высота', $fStyle);
        $c2 = $table->addCell($colWidth, $cellStyle);
        $c2->addText($textPillar, $fStyle);

        $section->addTextBreak(1);

        $this->addBlockTechnicalReport($section, $context);

        $section->addTextBreak(2);

        $cellStyle = ['valign' => 'center', 'marginLeft' => 100];
        $tableSign = $section->addTable(['indent' => new TblWidthType(Converter::cmToTwip(1), TblWidth::TWIP)]);
        $tableSign->addRow(Converter::cmToTwip(3));
        $c1 = $tableSign->addCell(Converter::cmToTwip(8), $cellStyle);
        $c1->addText('Главный инженер проекта', $fStyle);
        $c2 = $tableSign->addCell(Converter::cmToTwip(5.5), $cellStyle);
        $c2->addImage($this->projectDir . '/static_image/sign_seal_DA.png', [
            'width' => Converter::cmToPoint(4),
            'height' => Converter::cmToPoint(4),
            'wrappingStyle' => 'inline',
            'alignment' => Jc::CENTER,
        ]);
        $c3 = $tableSign->addCell(Converter::cmToTwip(4), $cellStyle);
        $c3->addText("Лобанов Д.А.\nП-087288", $fStyle);
    }

    /**
     * Добавляет строку вида «Метка: Значение» с выравниванием по центру.
     */
    private function addBlockTechnicalReport(Section $section, ReportContext $context): void
    {
        $pStyle = [
            'alignment' => Jc::CENTER,
            'spaceAfter' => 0,
            'line' => 276,
            'lineRule' => 'exact',
            'lineHeight' => 1.15,
        ];

        $section->addText('Техническое заключение', [
            'size' => 22,
            'name' => 'Times New Roman',
            'italic' => true,
            'bold' => true,
            'line' => 276,
            'lineRule' => 'auto',
        ], $pStyle);
        $section->addTextBreak(1);

        $operator = '';
        foreach ($context->calculation?->getCalculationEquipments()?->toArray() as $equipment) {
            if ($equipment->getEquipmentGroup()->isDismant()) {
                $operator = $equipment->getOperator();
                break;
            }
        }

        $fStyle = [
            'size' => 16,
            'name' => 'Times New Roman',
            'italic' => true,
        ];
        $pStyle = [
            'alignment' => Jc::CENTER,
            'line' => 240,
            'lineRule' => 'auto',
            'lineHeight' => 1,

        ];

        $section->addText("о несущей способности конструкций опоры в связи", $fStyle, $pStyle);
        $section->addText("с размещением оборудования сотовой связи", $fStyle, $pStyle);
        $section->addText($operator, $fStyle, $pStyle);
    }
}
