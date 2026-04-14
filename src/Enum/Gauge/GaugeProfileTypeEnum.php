<?php

declare(strict_types=1);

namespace App\Enum\Gauge;

/**
 * Коды типов стальных профилей.
 * Значения соответствуют полю `code` в таблице gauge_profile_type.
 */
enum GaugeProfileTypeEnum: string
{
    case ANGLE_EQUAL   = 'ANGLE_EQUAL';    // Уголок равнополочный (ГОСТ 8509-93)
    case ANGLE_UNEQUAL = 'ANGLE_UNEQUAL';  // Уголок неравнополочный (ГОСТ 8510-86)
    case CHANNEL       = 'CHANNEL';        // Швеллер (ГОСТ 8240-97)
    case I_BEAM        = 'I_BEAM';         // Двутавр (ГОСТ 8239-89)
    case PIPE_ROUND    = 'PIPE_ROUND';     // Труба круглая (ГОСТ 10704-91 / 8734-75)
    case PIPE_SQUARE   = 'PIPE_SQUARE';    // Труба профильная квадратная (ГОСТ 30245-2003)
    case CIRCLE        = 'CIRCLE';         // Круг / сплошной прокат (ГОСТ 2590-2006)
    case SHEET         = 'SHEET';          // Лист

    public function label(): string
    {
        return match($this) {
            self::ANGLE_EQUAL   => 'Уголок равнополочный',
            self::ANGLE_UNEQUAL => 'Уголок неравнополочный',
            self::CHANNEL       => 'Швеллер',
            self::I_BEAM        => 'Двутавр',
            self::PIPE_ROUND    => 'Труба круглая',
            self::PIPE_SQUARE   => 'Труба профильная квадратная',
            self::CIRCLE        => 'Круг',
            self::SHEET         => 'Лист',
        };
    }

    /** Возвращает массив ['Наименование' => 'КОД'] для Symfony Form ChoiceType. */
    public static function choices(): array
    {
        $choices = [];
        foreach (self::cases() as $case) {
            $choices[$case->label()] = $case->value;
        }
        return $choices;
    }
}
