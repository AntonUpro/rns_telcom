<?php

declare(strict_types=1);

namespace App\Enum\Calculation;

enum ResultTableTypeEnum: string
{
    case PILLAR_FORCES = 'pillar_forces';
    case CRACK_OPENING = 'crack_opening';
    case BRACE_STRESS = 'brace_stress';
    case SUPERSTRUCTURE_STRESS = 'superstructure_stress';
    case PLATFORM_FORCES = 'platform_forces';
    case BASE_PILLAR_FORCES = 'base_forces';
    case DEFORMATION = 'deformation';
    case FOUNDATION = 'foundation';

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
            self::BASE_PILLAR_FORCES => 'Максимальные усилия в основании опоры',
            self::DEFORMATION => 'Деформации опоры',
            self::FOUNDATION => 'Результаты расчёта основания опоры',
        };
    }

    // Формулировка типа усиления которое необходимо выполнить для опоры
    public function constructFormulation(): string
    {
        return match ($this) {
            self::PILLAR_FORCES => 'выполнить усиление ствола опоры',
            self::BRACE_STRESS => 'выполнить усиление подкосов опоры',
            self::PLATFORM_FORCES => 'выполнить усиление площадки опоры',
            self::SUPERSTRUCTURE_STRESS => 'выполнить усиление надстройки опоры',
            self::BASE_PILLAR_FORCES => 'выполнить усиление основания опоры',
            self::DEFORMATION => 'выполнить усиление ствола опоры',
            self::FOUNDATION => 'выполнить усиление фундамента опоры',
        };
    }
}
