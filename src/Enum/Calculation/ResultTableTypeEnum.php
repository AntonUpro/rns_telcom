<?php

declare(strict_types=1);

namespace App\Enum\Calculation;

enum ResultTableTypeEnum: string
{
    case PILLAR_FORCES         = 'pillar_forces';
    case CRACK_OPENING         = 'crack_opening';
    case BRACE_STRESS          = 'brace_stress';
    case SUPERSTRUCTURE_STRESS = 'superstructure_stress';
    case PLATFORM_FORCES       = 'platform_forces';
    case BASE_FORCES           = 'base_forces';
    case DEFORMATION           = 'deformation';
    case FOUNDATION            = 'foundation';

    public function isOptional(): bool
    {
        return match ($this) {
            self::PILLAR_FORCES, self::CRACK_OPENING => false,
            default                                   => true,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::PILLAR_FORCES         => 'Максимальные усилия в стволе опоры',
            self::CRACK_OPENING         => 'Максимальное раскрытие трещин в стволе опоры',
            self::BRACE_STRESS          => 'Максимальные напряжения в элементах подкосов',
            self::SUPERSTRUCTURE_STRESS => 'Максимальные напряжения в элементах поясов надстройки',
            self::PLATFORM_FORCES       => 'Максимальные усилия в площадке и стойке',
            self::BASE_FORCES           => 'Максимальные усилия в основании опоры',
            self::DEFORMATION           => 'Деформации опоры',
            self::FOUNDATION            => 'Результаты расчёта основания опоры',
        };
    }
}
