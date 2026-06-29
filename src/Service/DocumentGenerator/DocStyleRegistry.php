<?php

declare(strict_types=1);

namespace App\Service\DocumentGenerator;

use PhpOffice\PhpWord\Shared\Converter;
use PhpOffice\PhpWord\SimpleType\Jc;
use PhpOffice\PhpWord\SimpleType\JcTable;

/**
 * Централизованный реестр стилей для генерации DOCX-отчётов.
 */
final class DocStyleRegistry
{
    // ─── Стили шрифтов ────────────────────────────────────────────────────────

    public static function sectionTitle(): array
    {
        return ['bold' => true, 'size' => 12, 'italic' => true, 'name' => 'Times New Roman'];
    }

    public static function normalText(): array
    {
        return ['size' => 12, 'italic' => true, 'name' => 'Times New Roman'];
    }

    public static function bold(): array
    {
        return ['bold' => true, 'size' => 10, 'name' => 'Times New Roman'];
    }

    public static function center(): array
    {
        return ['italic' => true, 'size' => 10, 'name' => 'Times New Roman'];
    }

    public static function italicCenter(): array
    {
        return ['italic' => true, 'size' => 10, 'name' => 'Times New Roman'];
    }

    // ─── Стили параграфов ─────────────────────────────────────────────────────

    public static function paragraphCenter(): array
    {
        return ['alignment' => Jc::CENTER, 'line-spacing' => 0];
    }

    public static function paragraphLeft(): array
    {
        return ['alignment' => Jc::START];
    }

    public static function paragraphRight(): array
    {
        return ['alignment' => Jc::END];
    }

    public static function paragraphLineSpacing(): array
    {
        return ['line-spacing' => 0];
    }

    public static function paragraphTitle(): array
    {
        return [
            'alignment'   => Jc::CENTER,
            'spaceBefore' => Converter::cmToTwip(0.5),
            'spaceAfter'  => 0,
        ];
    }

    // ─── Стили таблицы ────────────────────────────────────────────────────────

    public static function tableStyle(): array
    {
        return [
            'borderSize'  => 1,
            'borderColor' => '000000',
            'cellMargin'  => 50,
            'alignment'   => JcTable::CENTER,
        ];
    }

    // ─── Стили ячеек ──────────────────────────────────────────────────────────

    public static function headerCell(): array
    {
        return ['valign' => 'center'];
    }

    public static function categoryRow(): array
    {
        return ['valign' => 'center'];
    }

    public static function subcategoryRow(): array
    {
        return ['valign' => 'center'];
    }

    public static function dataCell(): array
    {
        return ['valign' => 'center'];
    }

    public static function dataCellSpan(int $span): array
    {
        return ['valign' => 'center', 'gridSpan' => $span];
    }

    // ─── Стили для полного отчёта ОТС ────────────────────────────────────────

    public static function sectionHeading(): array
    {
        return ['bold' => true, 'size' => 12, 'name' => 'Times New Roman'];
    }

    public static function subSectionHeading(): array
    {
        return ['bold' => true, 'size' => 11, 'name' => 'Times New Roman'];
    }

    public static function bodyText(): array
    {
        return ['size' => 12, 'italic' => true, 'name' => 'Times New Roman'];
    }

    public static function bodyTextBold(): array
    {
        return ['bold' => true, 'size' => 12, 'italic' => true, 'name' => 'Times New Roman'];
    }

    public static function titleTableTextUnderline(): array
    {
        return ['size' => 12, 'italic' => true, 'name' => 'Times New Roman', 'underline' => 'single', ];
    }

    public static function titleTableTextUnderlineBold(): array
    {
        return ['size' => 12, 'italic' => true, 'name' => 'Times New Roman', 'underline' => 'single', 'bold' => true];
    }

    public static function paragraphJustified(): array
    {
        return ['alignment' => Jc::BOTH, 'spaceAfter' => 0];
    }

    public static function paragraphIndent(): array
    {
        return ['alignment' => Jc::BOTH, 'indentation' => ['left' => 0, 'firstLine' => Converter::cmToTwip(1.25)], 'spaceAfter' => 0];
    }

    public static function tableStyleReport(): array
    {
        return [
            'borderSize'  => 1,
            'borderColor' => '000000',
            'cellMargin'  => 40,
            'alignment'   => JcTable::CENTER,
        ];
    }
}
