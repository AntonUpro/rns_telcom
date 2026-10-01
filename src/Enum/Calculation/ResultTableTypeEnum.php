<?php

declare(strict_types=1);

namespace App\Enum\Calculation;

enum ResultTableTypeEnum: string
{
    case PILLAR_FORCES = 'pillar_forces';
    case CRACK_OPENING = 'crack_opening';
    case BRACE_STRESS = 'brace_stress';
    case SUPERSTRUCTURE_STRESS = 'superstructure_stress';
    case SUPERSTRUCTURE_STABILITY_BELT = 'superstructure_stability_belt';
    case SUPERSTRUCTURE_STABILITY_BRACE = 'superstructure_stability_brace';
    case PLATFORM_FORCES = 'platform_forces';
    case BASE_PILLAR_FORCES = 'base_forces';
    case DEFORMATION = 'deformation';
    case FOUNDATION = 'foundation';
    case NATURAL_FREQUENCIES = 'natural_frequencies';

    // ─── Таблицы башни ───────────────────────────────────────────────────────
    case TOWER_BELT_STABILITY = 'tower_belt_stability';
    case TOWER_BRACE_STABILITY = 'tower_brace_stability';
    case TOWER_SPACER_STABILITY = 'tower_spacer_stability';
    case TOWER_DEFORMATION = 'tower_deformation';
    case TOWER_ANCHOR_BOLTS = 'tower_anchor_bolts';
    case TOWER_FLANGE_BOLTS = 'tower_flange_bolts';
    case TOWER_FOUNDATION_LOADS = 'tower_foundation_loads';
    case TOWER_LOAD_COMPARISON = 'tower_load_comparison';

    /** Таблицы башни, включённые по умолчанию для нового расчёта */
    public const TOWER_ENABLED_BY_DEFAULT = [
        self::TOWER_BELT_STABILITY,
        self::TOWER_BRACE_STABILITY,
        self::TOWER_SPACER_STABILITY,
        self::TOWER_DEFORMATION,
    ];

    public function isOptional(): bool
    {
        return match ($this) {
            self::PILLAR_FORCES, self::CRACK_OPENING => false,
            default => true,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::PILLAR_FORCES => 'Максимальные усилия в стволе опоры',
//            self::CRACK_OPENING => 'Максимальное раскрытие трещин в стволе опоры',
            self::BRACE_STRESS => 'Максимальные напряжения в элементах подкосов площадки',
            self::PLATFORM_FORCES => 'Максимальные напряжения в элементах площадки',
            self::SUPERSTRUCTURE_STRESS => 'Максимальные напряжения в элементах поясов надстройки',
            self::SUPERSTRUCTURE_STABILITY_BELT => 'Максимальные напряжения в поясах надстройки (устойчивость)',
            self::SUPERSTRUCTURE_STABILITY_BRACE => 'Максимальные напряжения в элементах раскосов надстройки (устойчивость)',
            self::BASE_PILLAR_FORCES => 'Максимальные усилия в основании опоры',
            self::DEFORMATION => 'Деформации опоры',
            self::FOUNDATION => 'Результаты расчёта основания опоры',
            self::NATURAL_FREQUENCIES => 'Расчёт значений частот собственных колебаний',
            self::TOWER_BELT_STABILITY => 'Максимальные напряжения в поясах башни',
            self::TOWER_BRACE_STABILITY => 'Максимальные напряжения в раскосах башни',
            self::TOWER_SPACER_STABILITY => 'Максимальные напряжения в распорках башни',
            self::TOWER_DEFORMATION => 'Перемещения верхних узлов опоры от нормативных нагрузок',
            self::TOWER_ANCHOR_BOLTS => 'Напряжения в анкерных болтах',
            self::TOWER_FLANGE_BOLTS => 'Напряжения в фланцевых болтах',
            self::TOWER_FOUNDATION_LOADS => 'Максимальные нагрузки, действующие на фундаменты',
            self::TOWER_LOAD_COMPARISON => 'Сравнение расчетных нагрузок с проектными',
        };
    }

    /**
     * Формулировка типа усиления, которое необходимо выполнить для опоры.
     *
     * @param float|null $topExceedMark для PILLAR_FORCES — самая верхняя отметка (м),
     *        на которой коэффициент использования по несущей способности ещё ≥ 1;
     *        если передана, дополняет формулировку словами «до отметки +N,NNN м».
     */
    public function constructFormulation(?float $topExceedMark = null): string
    {
        $pillarSuffix = ($this === self::PILLAR_FORCES && $topExceedMark !== null)
            ? sprintf(' до отметки %s м', self::formatMark($topExceedMark))
            : '';

        return match ($this) {
            self::PILLAR_FORCES => 'выполнить усиление ствола опоры' . $pillarSuffix,
            self::BRACE_STRESS => 'выполнить усиление подкосов опоры',
            self::PLATFORM_FORCES => 'выполнить усиление площадки опоры',
            self::SUPERSTRUCTURE_STRESS => 'выполнить усиление надстройки опоры',
            self::SUPERSTRUCTURE_STABILITY_BELT => 'выполнить усиление надстройки опоры',
            self::SUPERSTRUCTURE_STABILITY_BRACE => 'выполнить усиление надстройки опоры',
            self::BASE_PILLAR_FORCES => 'выполнить усиление основания опоры',
            self::DEFORMATION => 'выполнить усиление ствола опоры',
            self::FOUNDATION => 'выполнить усиление фундамента опоры',
            self::NATURAL_FREQUENCIES => 'выполнить усиление надстройки опоры',
            self::TOWER_BELT_STABILITY => 'выполнить усиление поясов башни',
            self::TOWER_BRACE_STABILITY => 'выполнить усиление раскосов башни',
            self::TOWER_SPACER_STABILITY => 'выполнить усиление распорок башни',
            self::TOWER_DEFORMATION => 'выполнить усиление башни',
            self::TOWER_ANCHOR_BOLTS => 'выполнить усиление анкерных болтов',
            self::TOWER_FLANGE_BOLTS => 'выполнить усиление фланцевых соединений',
            self::TOWER_FOUNDATION_LOADS,
            self::TOWER_LOAD_COMPARISON => 'выполнить усиление фундаментов башни',
        };
    }

    private static function formatMark(float $mark): string
    {
        $formatted = number_format(abs($mark), 3, ',', '');

        return match (true) {
            $mark > 0 => '+' . $formatted,
            $mark < 0 => '-' . $formatted,
            default => $formatted,
        };
    }
}
