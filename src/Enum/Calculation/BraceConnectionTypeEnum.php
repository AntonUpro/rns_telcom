<?php

declare(strict_types=1);

namespace App\Enum\Calculation;

enum BraceConnectionTypeEnum: string
{
    case WELDED_OR_BOLTS = 'welded_or_bolts';
    case SINGLE_BOLT_OR_GUSSET = 'single_bolt_or_gusset';

    public function label(): string
    {
        return match ($this) {
            self::WELDED_OR_BOLTS => 'а) сварными швами либо двумя болтами и более',
            self::SINGLE_BOLT_OR_GUSSET => 'б) одним болтом или через фасонку',
        };
    }

    public function shortLabel(): string
    {
        return match ($this) {
            self::WELDED_OR_BOLTS => 'а)',
            self::SINGLE_BOLT_OR_GUSSET => 'б)',
        };
    }

    public static function toOptions(): array
    {
        return array_map(
            fn(self $case) => ['value' => $case->value, 'label' => $case->shortLabel()],
            self::cases()
        );
    }
}
