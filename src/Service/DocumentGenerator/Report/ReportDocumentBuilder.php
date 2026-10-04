<?php

declare(strict_types=1);

namespace App\Service\DocumentGenerator\Report;

use App\Service\DocumentGenerator\DocStyleRegistry;
use PhpOffice\PhpWord\ComplexType\TblWidth as TblWidthType;
use PhpOffice\PhpWord\Element\Section;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Shared\Converter;
use PhpOffice\PhpWord\SimpleType\Jc;
use PhpOffice\PhpWord\SimpleType\TblWidth;
use PhpOffice\PhpWord\Style\Language;
use RuntimeException;
use ZipArchive;

/**
 * Общий каркас DOCX-отчётов ОТС (столб, башня).
 *
 * Отвечает за всё, что одинаково во всех отчётах: поля страницы, стили заголовков,
 * содержание, штамп в нижнем колонтитуле, нумерацию разделов/приложений,
 * рамку страницы и сохранение файла.
 *
 * Документ состоит из двух секций:
 *   0 — титульные листы;
 *   1 — основная часть (со штампом).
 */
final class ReportDocumentBuilder
{
    public const TITLE_SECTION_INDEX = 0;
    public const MAIN_SECTION_INDEX = 1;

    public function createDocument(): PhpWord
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
            'spaceAfter' => 0,
            'indentation' => [
                'left' => (int)Converter::cmToTwip(1),
                'right' => (int)Converter::cmToTwip(1),
                'hanging' => null,
            ],
        ]);

        $phpWord->addTitleStyle(2, [
            'bold' => true,
            'size' => 12,
            'name' => 'Times New Roman',
            'italic' => true,
        ], [
            'alignment' => Jc::BOTH,
            'indentation' => [
                'left' => (int)Converter::cmToTwip(1),
                'right' => (int)Converter::cmToTwip(1),
                'hanging' => null,
            ],
            'spaceAfter' => 0,
        ]);

        // Секция с титулами
        $phpWord->addSection([
            'paperSize' => 'A4',
            'marginLeft' => Converter::cmToTwip(2.0),
            'marginRight' => Converter::cmToTwip(0.5),
            'marginTop' => Converter::cmToTwip(1.5),
            'marginBottom' => Converter::cmToTwip(2.0),
            'footerHeight' => Converter::cmToTwip(0.5),
        ]);
        // Главная секция
        $section = $phpWord->addSection([
            'paperSize' => 'A4',
            'marginLeft' => Converter::cmToTwip(2.0),
            'marginRight' => Converter::cmToTwip(0.5),
            'marginTop' => Converter::cmToTwip(1.5),
            'marginBottom' => Converter::cmToTwip(2.0),
            'footerHeight' => Converter::cmToTwip(0.5),
        ]);
        $section->addHeader();
        $phpWord->setDefaultParagraphStyle([
            'line-spacing' => 150,
            'spaceAfter' => 0,
        ]);

        return $phpWord;
    }

    public function addTableOfContents(Section $section): void
    {
        $section->addTitle('СОДЕРЖАНИЕ', 1);
        $section->addTOC(
            array_merge(DocStyleRegistry::sectionTitle(), [
                'paragraph' => [
                    'indentation' => [
                        'left'    => (int) Converter::cmToTwip(1),
                        'right'   => (int) Converter::cmToTwip(1),
                        'hanging' => null,
                    ],
                ],
            ]),
            // tab = ширина текста (18.5cm) − правый отступ (1cm) = 17.5cm от левого поля
            ['position' => (int) Converter::cmToTwip(17.5)],
            1,
            2,
        );
        $section->addPageBreak();
    }

    /** Заголовок раздела «N. НАЗВАНИЕ» с автоинкрементом номера */
    public function addSection(Section $section, int &$number, string $title): void
    {
        $number++;
        $section->addTitle(sprintf('%d. %s', $number, $title), 1);
    }

    /** Заголовок приложения «ПРИЛОЖЕНИЕ N. НАЗВАНИЕ» с автоинкрементом номера */
    public function addAppendix(Section $section, int &$number, string $title): void
    {
        $number++;
        $section->addTitle(sprintf('ПРИЛОЖЕНИЕ %d. %s', $number, $title), 1);
    }

    public function addFooterStamp(Section $section, ReportContext $context): void
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
            'indent' => new TblWidthType(Converter::cmToTwip(0), TblWidth::TWIP),
            'alignment' => 'left',
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
            $context->calculation?->getObjectCode(),
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
        $stamp->addCell(Converter::cmToTwip(1.0), ['vMerge' => 'continue']);

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

    /**
     * Сохраняет документ в DOCX и добавляет рамку страницы и стили содержания.
     *
     * @return string Абсолютный путь к созданному файлу
     *
     * @throws RuntimeException если не удалось создать директорию или обработать файл
     */
    public function save(PhpWord $phpWord, string $outputDir, string $fileName): string
    {
        if (! is_dir($outputDir) && ! mkdir($outputDir, 0755, true)) {
            throw new RuntimeException(sprintf('Не удалось создать директорию "%s"', $outputDir));
        }

        $filePath = sprintf('%s/%s', rtrim($outputDir, '/'), $fileName);
        IOFactory::createWriter($phpWord, 'Word2007')->save($filePath);
        $this->injectPageBorders($filePath);

        return $filePath;
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

        // offsetFrom="text": space=0 ставит рамку точно на границу текстового поля (поля страницы).
        // offsetFrom="page" вызывает смещение левой рамки в Word из-за ограничений области печати.
        $borders = '<w:pgBorders w:offsetFrom="text">'
            . '<w:top w:val="single" w:sz="6" w:space="20" w:color="000000"/>'
            . '<w:left w:val="single" w:sz="6" w:space="0" w:color="000000"/>'
            . '<w:bottom w:val="single" w:sz="6" w:space="0" w:color="000000"/>'
            . '<w:right w:val="single" w:sz="6" w:space="0" w:color="000000"/>'
            . '</w:pgBorders>';

        $xml = str_replace('</w:sectPr>', $borders . '</w:sectPr>', $xml);

        // Пометить TOC-поле как dirty, чтобы Word обновил номера страниц при открытии
        $xml = str_replace(
            '<w:fldChar w:fldCharType="begin"/>',
            '<w:fldChar w:fldCharType="begin" w:dirty="true"/>',
            $xml,
        );

        $zip->addFromString('word/document.xml', $xml);

        // Инжектируем именованные стили TOC 1 / TOC 2 в styles.xml.
        // Word при обновлении полей применяет именно эти стили — без них форматирование сбрасывается.
        $stylesXml = $zip->getFromName('word/styles.xml');
        if ($stylesXml !== false && ! str_contains($stylesXml, 'w:styleId="TOC1"')) {
            $leftTwip = (int) Converter::cmToTwip(1); // 1 cm
            // tab = ширина текста (18.5cm) − правый отступ (1cm) = 17.5cm от левого поля
            $tabTwip  = (int) Converter::cmToTwip(17.5);
            $tocStyles = sprintf(
                '<w:style w:type="paragraph" w:styleId="TOC1">'
                    . '<w:name w:val="toc 1"/><w:basedOn w:val="Normal"/>'
                    . '<w:pPr>'
                        . '<w:spacing w:after="0"/>'
                        . '<w:ind w:left="%1$d" w:right="%1$d"/>'
                        . '<w:tabs><w:tab w:val="right" w:leader="dot" w:pos="%3$d"/></w:tabs>'
                    . '</w:pPr>'
                    . '<w:rPr>'
                        . '<w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:cs="Times New Roman"/>'
                        . '<w:b/><w:i/><w:sz w:val="24"/><w:szCs w:val="24"/>'
                    . '</w:rPr>'
                . '</w:style>'
                . '<w:style w:type="paragraph" w:styleId="TOC2">'
                    . '<w:name w:val="toc 2"/><w:basedOn w:val="Normal"/>'
                    . '<w:pPr>'
                        . '<w:spacing w:after="0"/>'
                        . '<w:ind w:left="%2$d" w:right="%1$d"/>'
                        . '<w:tabs><w:tab w:val="right" w:leader="dot" w:pos="%3$d"/></w:tabs>'
                    . '</w:pPr>'
                    . '<w:rPr>'
                        . '<w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:cs="Times New Roman"/>'
                        . '<w:b/><w:i/><w:sz w:val="24"/><w:szCs w:val="24"/>'
                    . '</w:rPr>'
                . '</w:style>',
                $leftTwip,
                $leftTwip + 200, // TOC 2: base + один уровень отступа (TOCStyle::$indent = 200 twip)
                $tabTwip,
            );
            $stylesXml = str_replace('</w:styles>', $tocStyles . '</w:styles>', $stylesXml);
            $zip->addFromString('word/styles.xml', $stylesXml);
        }

        $zip->close();
    }
}
