<?php

declare(strict_types=1);

namespace App\Enum\Calculation;

enum SchemeNumberEnum: string
{
    case A = 'a';
    case B = 'b';
    case V = 'v';
    case G = 'g';
    case D = 'd';
    case E = 'e';

    public function label(): string
    {
        return match ($this) {
            self::A => 'а',
            self::B => 'б',
            self::V => 'в',
            self::G => 'г',
            self::D => 'д',
            self::E => 'е',
        };
    }

    public static function toOptions(): array
    {
        return array_map(
            fn(self $case) => ['value' => $case->value, 'label' => $case->label()],
            self::cases()
        );
    }
}
