<?php

declare(strict_types=1);

namespace App\Enum\Calculation;

enum ResultTableTypeEnum: string
{
    case TABLE_1 = 'table1';
    case TABLE_2 = 'table2';
    case TABLE_3 = 'table3';
    case TABLE_4 = 'table4';
    case TABLE_5 = 'table5';
    case TABLE_6 = 'table6';
    case TABLE_7 = 'table7';
    case TABLE_8 = 'table8';

    public function isOptional(): bool
    {
        return match ($this) {
            self::TABLE_1, self::TABLE_2 => false,
            default                      => true,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::TABLE_1 => 'Максимальные усилия в стволе опоры',
            self::TABLE_2 => 'Максимальное раскрытие трещин в стволе опоры',
            self::TABLE_3 => 'Максимальные напряжения в элементах подкосов',
            self::TABLE_4 => 'Максимальные напряжения в элементах поясов надстройки',
            self::TABLE_5 => 'Максимальные усилия в площадке',
            self::TABLE_6 => 'Максимальные усилия в основании опоры',
            self::TABLE_7 => 'Деформации опоры',
            self::TABLE_8 => 'Результаты расчёта основания опоры',
        };
    }
}
