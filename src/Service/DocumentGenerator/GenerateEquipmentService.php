<?php

declare(strict_types=1);

namespace App\Service\DocumentGenerator;

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Shared\Converter;
use PhpOffice\PhpWord\SimpleType\TblWidth;
use PhpOffice\PhpWord\Style\Table;
use PhpOffice\PhpWord\Style\Cell;
use PhpOffice\PhpWord\Style\Font;
use PhpOffice\PhpWord\SimpleType\TextAlignment;
use PhpOffice\PhpWord\Writer\Word2007\Element\ParagraphAlignment;
use PhpOffice\PhpWord\SimpleType\Jc;
use PhpOffice\PhpWord\Writer\Word2007\Element\TableAlignment;
use PhpOffice\PhpWord\SimpleType\JcTable;

class GenerateEquipmentService
{
    public function generate()
    {
        // Создаем новый документ
        $phpWord = IOFactory::load(__DIR__ . '/../../../template_doc/template.docx');

        // Настройка шрифтов
        $phpWord->setDefaultFontName('Times New Roman');
        $phpWord->setDefaultFontSize(10);

        // Стили для заголовков
        $sectionTitleStyle = ['bold' => true, 'size' => 12, 'alignment' => TextAlignment::AUTO, 'italic' => true, 'spaceBefore' => Converter::cmToTwip(1.5)];
        $tableTitleStyle = ['bold' => true, 'size' => 10, 'alignment' => TextAlignment::AUTO];
        $normalTextStyle = ['size' => 12, 'alignment' => TextAlignment::AUTO, 'italic' => true, 'spaceBefore' => Converter::cmToTwip(1.5), 'space' => Converter::cmToTwip(0.5)];
        $boldTextStyle = ['bold' => true, 'size' => 10, 'alignment' => TextAlignment::AUTO];
        $centerTextStyle = ['size' => 10, 'alignment' => TextAlignment::CENTER];
        $boldCenterTextStyle = ['bold' => true, 'size' => 10, 'alignment' => TextAlignment::CENTER];

        $section = $phpWord->getSection(0);

//        $section = $phpWord->addSection([
//            'pageSizeW' => Converter::cmToTwip(21),   // Ширина А4
//            'pageSizeH' => Converter::cmToTwip(29.7),   // Высота А4
//
//            'marginLeft' => Converter::cmToTwip(2.0),    // 20 мм слева (подшивка)
//            'marginRight' => Converter::cmToTwip(0.5),    // 5 мм справа
//            'marginTop' => Converter::cmToTwip(0.5),    // 5 мм сверху
//            'marginBottom' => Converter::cmToTwip(0.5),    // 5 мм снизу
//
//            'borderSize'      => 12,                       // Толщина рамки: 12/8 = 1.5 pt ≈ 0.53 мм
//            'borderColor'     => '000000',                 // Цвет рамки: чёрный
//            'borderTopSize'   => 12,
//            'borderBottomSize'=> 12,
//            'borderLeftSize'  => 12,
//            'borderRightSize' => 12,
//            'borderTopColor'  => '000000',
//            'borderBottomColor'=> '000000',
//            'borderLeftColor' => '000000',
//            'borderRightColor'=> '000000',
//
//            'borderSpacing'   => 41,
//            'headerLine'      => false,
//
//            'headerHeight' => Converter::cmToTwip(0),
//            'footerHeight' => Converter::cmToTwip(0.5),
//        ]);

//        $footer = $section->addFooter();

// Структура штампа для формата А4 (185×30 мм)
//        $stamp = $footer->addTable([
//            'width' => Converter::cmToTwip(185),
//            'unit' => TblWidth::TWIP,
//            'borderSize' => 12, // Внутренние линии тоньше
//            'borderColor' => '000000',
//            'cellMargin' => 50, // Отступ внутри ячеек
//            'borderBottomSize' => 0,
//        ]);

//// Строки штампа по ГОСТ 2.104-2006 (упрощённая структура)
//        $stamp->addRow(Converter::cmToTwip(0.5));
//        $stamp->addCell(Converter::cmToTwip(0.7));
//        $stamp->addCell(Converter::cmToTwip(1.0));
//        $stamp->addCell(Converter::cmToTwip(2.3));
//        $stamp->addCell(Converter::cmToTwip(1.5));
//        $stamp->addCell(Converter::cmToTwip(1.0));
//        $stamp->addCell(Converter::cmToTwip(11.0), ['vMerge' => 'restart']);
//        $stamp->addCell(Converter::cmToTwip(1.0))->addText('Лист', ['size' => 8, 'bold' => true], ['alignment' => Jc::CENTER]);
//
//        $stamp->addRow(Converter::cmToTwip(0.5));
//        $stamp->addCell(Converter::cmToTwip(0.7));
//        $stamp->addCell(Converter::cmToTwip(1.0));
//        $stamp->addCell(Converter::cmToTwip(2.3));
//        $stamp->addCell(Converter::cmToTwip(1.5));
//        $stamp->addCell(Converter::cmToTwip(1.0));
//        $stamp->addCell(Converter::cmToTwip(11.0), ['vMerge' => 'continue']);
//        $stamp->addCell(Converter::cmToTwip(1.0));
//
//        $stamp->addRow(Converter::cmToTwip(0.5));
//        $stamp->addCell(Converter::cmToTwip(0.7))->addText('Изм', ['size' => 7, 'bold' => true], ['alignment' => Jc::CENTER]);
//        $stamp->addCell(Converter::cmToTwip(1.0))->addText('Кол.уч', ['size' => 7, 'bold' => true], ['alignment' => Jc::CENTER]);
//        $stamp->addCell(Converter::cmToTwip(2.3))->addText('№ док.', ['size' => 7, 'bold' => true], ['alignment' => Jc::CENTER]);
//        $stamp->addCell(Converter::cmToTwip(1.5))->addText('Подпись', ['size' => 7, 'bold' => true], ['alignment' => Jc::CENTER]);
//        $stamp->addCell(Converter::cmToTwip(1.0))->addText('Дата', ['size' => 7, 'bold' => true], ['alignment' => Jc::CENTER]);
//        $stamp->addCell(Converter::cmToTwip(11.0), ['vMerge' => 'continue'])->addText('Дата', ['size' => 7, 'bold' => true], ['alignment' => Jc::CENTER]);
//        $stamp->addCell(Converter::cmToTwip(1.0))->addText('3', ['size' => 7, 'bold' => true], ['alignment' => Jc::CENTER]);

        // Заголовок раздела
        $section->addText('ВЕТРОВОЕ ДАВЛЕНИЕ НА ОБОРУДОВАНИЕ', $sectionTitleStyle);
        $section->addTextBreak(1);

        // Текст перед таблицей
        $section->addText(
            'Состав оборудования принят в соответствии с предоставленной документацией и результатами натурного обследования:',
            $normalTextStyle
        );
        $section->addTextBreak(1);

        // Создаем таблицу
        $table = $section->addTable([
            'borderSize' => 1,
            'borderColor' => '000000',
            'cellMargin' => 50,
            'alignment' => JcTable::CENTER
        ]);

        // Данные для таблицы
        $equipmentData = [
            [
                'category' => 'Существующее',
                'items' => [
                    [
                        'subcategory' => 'Антенны РРЛ',
                        'rows' => [
                            [
                                'name' => 'Антенна РРЛ 0.6 ООО "Т2 Мобайл" (Ø600 мм; 17 кг)',
                                'N' => '1',
                                'H_podvesa' => '29,000',
                                'k_ze' => '1,36',
                                'Aob' => '0,28',
                                'At_s' => '0,00',
                                'Wo' => '23,46',
                                'Y' => '1,4',
                                'C_xoo' => '2,34',
                                'K_lambda' => '0,60',
                                'C_x_ob' => '1,41',
                                'C_x_t_s' => '1,3',
                                'mu' => '1,0',
                                'P' => '17,8'
                            ]
                        ]
                    ],
                    [
                        'subcategory' => 'Панельные антенны',
                        'rows' => [
                            [
                                'name' => 'HBXX-6517DS-VTM ООО "Т2 Мобайл" (1903х305х166 мм; 18,5 кг)',
                                'N' => '3',
                                'H_podvesa' => '27,000',
                                'k_ze' => '1,34',
                                'Aob' => '0,58',
                                'At_s' => '0,00',
                                'Wo' => '23,46',
                                'Y' => '1,4',
                                'C_xoo' => '2,00',
                                'K_lambda' => '0,68',
                                'C_x_ob' => '1,35',
                                'C_x_t_s' => '1,3',
                                'mu' => '0,8',
                                'P' => '27,6'
                            ]
                        ]
                    ],
                    [
                        'subcategory' => 'Радиоблоки, радиомодули, термошкафы',
                        'rows' => [
                            [
                                'name' => 'FRGT ООО "Т2 Мобайл" (560х492х133 мм; 25 кг)',
                                'N' => '1',
                                'H_podvesa' => '24,000',
                                'k_ze' => '1,30',
                                'Aob' => '0,28',
                                'At_s' => '0,00',
                                'Wo' => '23,46',
                                'Y' => '1,4',
                                'C_xoo' => '2,03',
                                'K_lambda' => '0,60',
                                'C_x_ob' => '1,23',
                                'C_x_t_s' => '1,3',
                                'mu' => '0,7',
                                'P' => '10,1'
                            ],
                            [
                                'name' => 'FXEA ООО "Т2 Мобайл" (560х447х133 мм; 25 кг)',
                                'N' => '2',
                                'H_podvesa' => '24,000',
                                'k_ze' => '1,30',
                                'Aob' => '0,25',
                                'At_s' => '0,00',
                                'Wo' => '23,46',
                                'Y' => '1,4',
                                'C_xoo' => '2,03',
                                'K_lambda' => '0,61',
                                'C_x_ob' => '1,23',
                                'C_x_t_s' => '1,3',
                                'mu' => '0,7',
                                'P' => '9,2'
                            ]
                        ]
                    ]
                ]
            ],
            [
                'category' => 'Проектируемое',
                'items' => [
                    [
                        'subcategory' => 'Антенны РРЛ',
                        'rows' => [
                            [
                                'name' => 'Антенна РРЛ 0,6 ПАО "Вымпелком" (Ø600 мм; 17 кг)',
                                'N' => '1',
                                'H_podvesa' => '29,000',
                                'k_ze' => '1,36',
                                'Aob' => '0,28',
                                'At_s' => '0,00',
                                'Wo' => '23',
                                'Y' => '1,4',
                                'C_xoo' => '2,34',
                                'K_lambda' => '0,60',
                                'C_x_ob' => '1,41',
                                'C_x_t_s' => '1,3',
                                'mu' => '1,0',
                                'P' => '17,8'
                            ]
                        ]
                    ],
                    [
                        'subcategory' => 'Панельные антенны',
                        'rows' => [
                            [
                                'name' => 'RX1004M6R015 ПАО "Вымпелком" (1999х469х198 мм; 34 кг)',
                                'N' => '3',
                                'H_podvesa' => '20,000',
                                'k_ze' => '1,25',
                                'Aob' => '0,94',
                                'At_s' => '0,00',
                                'Wo' => '23',
                                'Y' => '1,4',
                                'C_xoo' => '2,00',
                                'K_lambda' => '0,66',
                                'C_x_ob' => '1,32',
                                'C_x_t_s' => '1,3',
                                'mu' => '0,8',
                                'P' => '40,7'
                            ]
                        ]
                    ],
                    [
                        'subcategory' => 'Радиоблоки, радиомодули, термошкафы',
                        'rows' => [
                            [
                                'name' => 'RRU5904 ПАО "Вымпелком" (400х300х150 мм; 22 кг)',
                                'N' => '3',
                                'H_podvesa' => '20,000',
                                'k_ze' => '1,25',
                                'Aob' => '0,12',
                                'At_s' => '0,00',
                                'Wo' => '23',
                                'Y' => '1,4',
                                'C_xoo' => '2,14',
                                'K_lambda' => '0,61',
                                'C_x_ob' => '1,31',
                                'C_x_t_s' => '1,3',
                                'mu' => '0,7',
                                'P' => '4,5'
                            ],
                            [
                                'name' => 'Guardian M VK23 A1 FC 18U TG ПАО "Вымпелком" (870х650х700 мм; 100 кг)',
                                'N' => '1',
                                'H_podvesa' => '0,000',
                                'k_ze' => '0,00',
                                'Aob' => '0,57',
                                'At_s' => '0,00',
                                'Wo' => '23',
                                'Y' => '1,4',
                                'C_xoo' => '2,29',
                                'K_lambda' => '0,61',
                                'C_x_ob' => '1,40',
                                'C_x_t_s' => '1,3',
                                'mu' => '0,0',
                                'P' => '0,0'
                            ]
                        ]
                    ]
                ]
            ],
            [
                'category' => 'Демонтируемое',
                'items' => [
                    [
                        'subcategory' => 'Панельные антенны',
                        'rows' => [
                            [
                                'name' => 'HBX-6517DS-VTM ООО "Т2 Мобайл" (1902х166х83 мм; 6,2 кг)',
                                'N' => '2',
                                'H_podvesa' => '27,000',
                                'k_ze' => '1,34',
                                'Aob' => '0,32',
                                'At_s' => '0,20',
                                'Wo' => '23',
                                'Y' => '1,4',
                                'C_xoo' => '2,00',
                                'K_lambda' => '0,71',
                                'C_x_ob' => '1,42',
                                'C_x_t_s' => '1,3',
                                'mu' => '0,8',
                                'P' => '24,9'
                            ]
                        ]
                    ]
                ]
            ]
        ];


        // Ширины колонок (в twips: 1 см ≈ 567 twips)
        $colWidths = [
            2200,  // Обозначение оборудования, габариты
            600,   // N, шт
            600,  // Нподвеса, м
            600,   // k(ze)
            600,   // Aоб, м²
            600,   // Ат/с, м²
            600,   // Wo, кг/м²
            600,   // Υ
            600,   // Сx∞
            600,   // Kλ
            600,   // Сx(об)
            600,   // Сx(т/с)
            600,   // µ
            600   // Р, кг/ед.об.
        ];

        // Добавляем заголовок таблицы
        $this->addTableHeader($table, $colWidths, $boldCenterTextStyle);

        // Заполняем таблицу данными
        foreach ($equipmentData as $category) {
            // Добавляем строку с категорией (Существующее, Проектируемое, Демонтируемое)
            $table->addRow(400);
            $table->addCell(array_sum($colWidths), ['gridSpan' => 14, 'valign' => 'center', 'bgColor' => 'E0E0E0'])
                ->addText($category['category'], $boldCenterTextStyle);

            foreach ($category['items'] as $item) {
                // Добавляем строку с подкатегорией
                $table->addRow(400);
                $table->addCell(array_sum($colWidths), ['gridSpan' => 14, 'valign' => 'center', 'bgColor' => 'F0F0F0'])
                    ->addText($item['subcategory'], $boldCenterTextStyle);

                // Добавляем строки с оборудованием
                foreach ($item['rows'] as $row) {
                    $table->addRow(400);

                    $table->addCell($colWidths[0], ['valign' => 'center'])
                        ->addText($row['name'], $normalTextStyle);

                    $table->addCell($colWidths[1], ['valign' => 'center'])
                        ->addText($row['N'], $centerTextStyle);

                    $table->addCell($colWidths[2], ['valign' => 'center'])
                        ->addText($row['H_podvesa'], $centerTextStyle);

                    $table->addCell($colWidths[3], ['valign' => 'center'])
                        ->addText($row['k_ze'], $centerTextStyle);

                    $table->addCell($colWidths[4], ['valign' => 'center'])
                        ->addText($row['Aob'], $centerTextStyle);

                    $table->addCell($colWidths[5], ['valign' => 'center'])
                        ->addText($row['At_s'], $centerTextStyle);

                    $table->addCell($colWidths[6], ['valign' => 'center'])
                        ->addText($row['Wo'], $centerTextStyle);

                    $table->addCell($colWidths[7], ['valign' => 'center'])
                        ->addText($row['Y'], $centerTextStyle);

                    $table->addCell($colWidths[8], ['valign' => 'center'])
                        ->addText($row['C_xoo'], $centerTextStyle);

                    $table->addCell($colWidths[9], ['valign' => 'center'])
                        ->addText($row['K_lambda'], $centerTextStyle);

                    $table->addCell($colWidths[10], ['valign' => 'center'])
                        ->addText($row['C_x_ob'], $centerTextStyle);

                    $table->addCell($colWidths[11], ['valign' => 'center'])
                        ->addText($row['C_x_t_s'], $centerTextStyle);

                    $table->addCell($colWidths[12], ['valign' => 'center'])
                        ->addText($row['mu'], $centerTextStyle);

                    $table->addCell($colWidths[13], ['valign' => 'center'])
                        ->addText($row['P'], $centerTextStyle);
                }
            }
        }

        $section->addTextBreak(1);

        // Текст после таблицы
        $section->addText(
            'Ветровое давление Р[кг] приведено на единицу навесного антенного оборудования.',
            $normalTextStyle
        );
        $section->addTextBreak(1);

        // Добавляем таблицу с суммарными данными
        $summaryTable = $section->addTable([
            'borderSize' => 1,
            'borderColor' => '000000',
            'cellMargin' => 50,
            'alignment' => JcTable::CENTER
        ]);

        // Ширины колонок для сводной таблицы
        $summaryColWidths = [4000, 2000, 2000, 1500];

        // Заголовок сводной таблицы
        $summaryTable->addRow(400);
        $summaryTable->addCell($summaryColWidths[0], ['valign' => 'center'])
            ->addText('Принадлежность оборудования', $boldCenterTextStyle);
        $summaryTable->addCell($summaryColWidths[1], ['valign' => 'center'])
            ->addText('∑Aоборуд, м²', $boldCenterTextStyle);
        $summaryTable->addCell($summaryColWidths[2], ['valign' => 'center'])
            ->addText('∑Аоборуд*k(z)*z', $boldCenterTextStyle);
        $summaryTable->addCell($summaryColWidths[3], ['valign' => 'center'])
            ->addText('∑Вес, кг', $boldCenterTextStyle);

        // Данные сводной таблицы
        $summaryData = [
            ['До модернизации', '2,65', '179,05', '199,9'],
            ['После модернизации', '4,51', '282,63', '332,5']
        ];

        foreach ($summaryData as $row) {
            $summaryTable->addRow(400);
            $summaryTable->addCell($summaryColWidths[0], ['valign' => 'center'])
                ->addText($row[0], $boldTextStyle);
            $summaryTable->addCell($summaryColWidths[1], ['valign' => 'center'])
                ->addText($row[1], $centerTextStyle);
            $summaryTable->addCell($summaryColWidths[2], ['valign' => 'center'])
                ->addText($row[2], $centerTextStyle);
            $summaryTable->addCell($summaryColWidths[3], ['valign' => 'center'])
                ->addText($row[3], $centerTextStyle);
        }

        $section->addTextBreak(1);

        // Добавляем подзаголовок для следующей таблицы
        $section->addText('Кабельная трасса, кабельные полки и кабельрост, лестница:', $normalTextStyle);
        $section->addTextBreak(1);

        // Создаем третью таблицу
        $cableTable = $section->addTable([
            'borderSize' => 1,
            'borderColor' => '000000',
            'cellMargin' => 50,
            'alignment' => JcTable::CENTER
        ]);

        // Данные для таблицы кабельной трассы
        $cableData = [
            [
                '№ участка' => '1',
                'z, м' => '3,000',
                'k(ze)' => '0,75',
                'Wo, кг/м²' => '23,46',
                'Υ' => '1,4',
                'Кабельная трасса' => ['A, м²' => '0,08', 'сх' => '1,2', 'Р, кг' => '2,4'],
                'Кабельные полки, кабельрост' => ['A, м²' => '0,11', 'сх' => '1,4', 'Р, кг' => '3,7'],
                'Лестница' => ['A, м²' => '0,54', 'сх' => '1,4', 'Р, кг' => '18,6']
            ],
            [
                '№ участка' => '2',
                'z, м' => '8,000',
                'k(ze)' => '0,78',
                'Wo, кг/м²' => '23,46',
                'Υ' => '1,4',
                'Кабельная трасса' => ['A, м²' => '0,41', 'сх' => '1,2', 'Р, кг' => '12,5'],
                'Кабельные полки, кабельрост' => ['A, м²' => '0,18', 'сх' => '1,4', 'Р, кг' => '6,4'],
                'Лестница' => ['A, м²' => '0,90', 'сх' => '1,4', 'Р, кг' => '32,1']
            ],
            [
                '№ участка' => '3',
                'z, м' => '13,000',
                'k(ze)' => '1,01',
                'Wo, кг/м²' => '23,46',
                'Υ' => '1,4',
                'Кабельная трасса' => ['A, м²' => '0,41', 'сх' => '1,2', 'Р, кг' => '16,4'],
                'Кабельные полки, кабельрост' => ['A, м²' => '0,18', 'сх' => '1,4', 'Р, кг' => '8,4'],
                'Лестница' => ['A, м²' => '0,90', 'сх' => '1,4', 'Р, кг' => '41,9']
            ],
            [
                '№ участка' => '4',
                'z, м' => '18,000',
                'k(ze)' => '1,14',
                'Wo, кг/м²' => '23,46',
                'Υ' => '1,4',
                'Кабельная трасса' => ['A, м²' => '0,41', 'сх' => '1,2', 'Р, кг' => '18,4'],
                'Кабельные полки, кабельрост' => ['A, м²' => '0,18', 'сх' => '1,4', 'Р, кг' => '9,4'],
                'Лестница' => ['A, м²' => '0,90', 'сх' => '1,4', 'Р, кг' => '47,1']
            ],
            [
                '№ участка' => '5',
                'z, м' => '23,000',
                'k(ze)' => '1,26',
                'Wo, кг/м²' => '23,46',
                'Υ' => '1,4',
                'Кабельная трасса' => ['A, м²' => '0,41', 'сх' => '1,2', 'Р, кг' => '20,3'],
                'Кабельные полки, кабельрост' => ['A, м²' => '0,18', 'сх' => '1,4', 'Р, кг' => '10,4'],
                'Лестница' => ['A, м²' => '0,90', 'сх' => '1,4', 'Р, кг' => '52,0']
            ]
        ];

        // Ширины колонок для таблицы кабельной трассы
        $cableColWidths = [800, 800, 800, 1000, 600, 800, 800, 800, 800, 800, 800, 800, 800, 800];

        // Заголовок таблицы кабельной трассы
        $cableTable->addRow(400);
//        $table->addCell($cableColWidths[0], ['gridSpan' => 11, 'valign' => 'center'])
//            ->addText('Таблица 3', $boldCenterTextStyle);

        $cableTable->addRow(400);

        // Основной заголовок с объединением колонок
        $cableTable->addCell($cableColWidths[0], ['valign' => 'center'])
            ->addText('№ участка', $boldCenterTextStyle);
        $cableTable->addCell($cableColWidths[1], ['valign' => 'center'])
            ->addText('z, м', $boldCenterTextStyle);
        $cableTable->addCell($cableColWidths[2], ['valign' => 'center'])
            ->addText('k(ze)', $boldCenterTextStyle);
        $cableTable->addCell($cableColWidths[3], ['valign' => 'center'])
            ->addText('Wo, кг/м²', $boldCenterTextStyle);
        $cableTable->addCell($cableColWidths[4], ['valign' => 'center'])
            ->addText('Υ', $boldCenterTextStyle);

        // Подзаголовки для групп
        $cableTable->addCell($cableColWidths[5] * 3, ['gridSpan' => 3, 'valign' => 'center'])
            ->addText('Кабельная трасса', $boldCenterTextStyle);
        $cableTable->addCell($cableColWidths[8] * 3, ['gridSpan' => 3, 'valign' => 'center'])
            ->addText('Кабельные полки, кабельрост', $boldCenterTextStyle);
        $cableTable->addCell($cableColWidths[11] * 3, ['gridSpan' => 3, 'valign' => 'center'])
            ->addText('Лестница', $boldCenterTextStyle);

        $cableTable->addRow(400);

        // Строка с подзаголовками колонок
        $cells = ['№ участка', 'z, м', 'k(ze)', 'Wo, кг/м²', 'Υ',
            'A, м²', 'сх', 'Р, кг',
            'A, м²', 'сх', 'Р, кг',
            'A, м²', 'сх', 'Р, кг'];

        for ($i = 0; $i < 5; $i++) {
            $cableTable->addCell($cableColWidths[$i], ['valign' => 'center'])
                ->addText($cells[$i], $boldCenterTextStyle);
        }

        // Добавляем подзаголовки для каждой группы из 3 колонок
        for ($i = 0; $i < 3; $i++) {
            $cableTable->addCell($cableColWidths[5 + $i], ['valign' => 'center'])
                ->addText($cells[5 + $i], $boldCenterTextStyle);
            $cableTable->addCell($cableColWidths[8 + $i], ['valign' => 'center'])
                ->addText($cells[8 + $i], $boldCenterTextStyle);
            $cableTable->addCell($cableColWidths[11 + $i], ['valign' => 'center'])
                ->addText($cells[11 + $i], $boldCenterTextStyle);
        }

        // Заполняем таблицу данными
        foreach ($cableData as $row) {
            $cableTable->addRow(400);

            $cableTable->addCell($cableColWidths[0], ['valign' => 'center'])
                ->addText($row['№ участка'], $centerTextStyle);
            $cableTable->addCell($cableColWidths[1], ['valign' => 'center'])
                ->addText($row['z, м'], $centerTextStyle);
            $cableTable->addCell($cableColWidths[2], ['valign' => 'center'])
                ->addText($row['k(ze)'], $centerTextStyle);
            $cableTable->addCell($cableColWidths[3], ['valign' => 'center'])
                ->addText($row['Wo, кг/м²'], $centerTextStyle);
            $cableTable->addCell($cableColWidths[4], ['valign' => 'center'])
                ->addText($row['Υ'], $centerTextStyle);

            // Кабельная трасса
            $cableTable->addCell($cableColWidths[5], ['valign' => 'center'])
                ->addText($row['Кабельная трасса']['A, м²'], $centerTextStyle);
            $cableTable->addCell($cableColWidths[6], ['valign' => 'center'])
                ->addText($row['Кабельная трасса']['сх'], $centerTextStyle);
            $cableTable->addCell($cableColWidths[7], ['valign' => 'center'])
                ->addText($row['Кабельная трасса']['Р, кг'], $centerTextStyle);

            // Кабельные полки, кабельрост
            $cableTable->addCell($cableColWidths[8], ['valign' => 'center'])
                ->addText($row['Кабельные полки, кабельрост']['A, м²'], $centerTextStyle);
            $cableTable->addCell($cableColWidths[9], ['valign' => 'center'])
                ->addText($row['Кабельные полки, кабельрост']['сх'], $centerTextStyle);
            $cableTable->addCell($cableColWidths[10], ['valign' => 'center'])
                ->addText($row['Кабельные полки, кабельрост']['Р, кг'], $centerTextStyle);

            // Лестница
            $cableTable->addCell($cableColWidths[11], ['valign' => 'center'])
                ->addText($row['Лестница']['A, м²'], $centerTextStyle);
            $cableTable->addCell($cableColWidths[12], ['valign' => 'center'])
                ->addText($row['Лестница']['сх'], $centerTextStyle);
            $cableTable->addCell($cableColWidths[13], ['valign' => 'center'])
                ->addText($row['Лестница']['Р, кг'], $centerTextStyle);
        }

        $section->addTextBreak(1);

        // Добавляем подзаголовок для таблицы площадки и подкоса
        $section->addText('Площадка и подкос:', $normalTextStyle);
        $section->addTextBreak(1);

        // Таблица площадки и подкоса
        $platformTable = $section->addTable([
            'borderSize' => 1,
            'borderColor' => '000000',
            'cellMargin' => 50,
            'alignment' => JcTable::CENTER
        ]);

        // Сохраняем документ
        $filename = 'ветровое_давление_оборудование.docx';
        $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save($filename);

        echo "Документ успешно создан: $filename";

// Для скачивания файла
        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        header('Content-Disposition: attachment;filename="ветровое_давление_оборудование.docx"');
        header('Cache-Control: max-age=0');
        $objWriter->save('php://output');


    }

    // Функция для добавления заголовка таблицы
    private function addTableHeader($table, $colWidths, array $boldCenterTextStyle)
    {
        // Первая строка заголовка
        $table->addRow(400);

        // Первая ячейка - объединение 14 колонок
        $table->addCell($colWidths[0], ['gridSpan' => 14, 'valign' => 'center'])
            ->addText('Таблица 2', $boldCenterTextStyle);

        // Вторая строка заголовка
        $table->addRow(400);

        // Обозначение оборудования, габариты
        $table->addCell($colWidths[0], ['valign' => 'center'])
            ->addText('Обозначение оборудования, габариты', $boldCenterTextStyle);

        // N, шт
        $table->addCell($colWidths[1], ['valign' => 'center'])
            ->addText('N, шт', $boldCenterTextStyle);

        // Нподвеса, м
        $table->addCell($colWidths[2], ['valign' => 'center'])
            ->addText('Нподвеса, м', $boldCenterTextStyle);

        // k(ze)
        $table->addCell($colWidths[3], ['valign' => 'center'])
            ->addText('k(ze)', $boldCenterTextStyle);

        // Aоб, м²
        $table->addCell($colWidths[4], ['valign' => 'center'])
            ->addText('Aоб, м²', $boldCenterTextStyle);

        // Ат/с, м²
        $table->addCell($colWidths[5], ['valign' => 'center'])
            ->addText('Ат/с, м²', $boldCenterTextStyle);

        // Wo, кг/м²
        $table->addCell($colWidths[6], ['valign' => 'center'])
            ->addText('Wo, кг/м²', $boldCenterTextStyle);

        // Υ
        $table->addCell($colWidths[7], ['valign' => 'center'])
            ->addText('Υ', $boldCenterTextStyle);

        // Сx∞
        $table->addCell($colWidths[8], ['valign' => 'center'])
            ->addText('Сx∞', $boldCenterTextStyle);

        // Kλ
        $table->addCell($colWidths[9], ['valign' => 'center'])
            ->addText('Kλ', $boldCenterTextStyle);

        // Сx(об)
        $table->addCell($colWidths[10], ['valign' => 'center'])
            ->addText('Сx(об)', $boldCenterTextStyle);

        // Сx(т/с)
        $table->addCell($colWidths[11], ['valign' => 'center'])
            ->addText('Сx(т/с)', $boldCenterTextStyle);

        // µ
        $table->addCell($colWidths[12], ['valign' => 'center'])
            ->addText('µ', $boldCenterTextStyle);

        // Р, кг/ед.об.
        $table->addCell($colWidths[13], ['valign' => 'center'])
            ->addText('Р, кг/ед.об.', $boldCenterTextStyle);
    }
}
