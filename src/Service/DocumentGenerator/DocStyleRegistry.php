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
        return ['size' => 10, 'italic' => true, 'name' => 'Times New Roman'];
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
        return ['alignment' => Jc::CENTER];
    }

    public static function paragraphLeft(): array
    {
        return ['alignment' => Jc::START];
    }

    public static function paragraphRight(): array
    {
        return ['alignment' => Jc::END];
    }

    public static function paragraphTitle(): array
    {
        return [
            'alignment'   => Jc::CENTER,
            'spaceBefore' => Converter::cmToTwip(0.5),
            'spaceAfter'  => Converter::cmToTwip(0.2),
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
}
