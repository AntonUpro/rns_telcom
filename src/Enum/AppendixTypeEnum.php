<?php

declare(strict_types=1);

namespace App\Enum;

enum AppendixTypeEnum: string
{
    case CERTIFICATES        = 'certificates';
    case SRO_EXCERPT         = 'sro_excerpt';
    case NOPRIZ_NOTIFICATION = 'nopriz_notification';

    public function label(): string
    {
        return match($this) {
            self::CERTIFICATES        => 'Сертификаты',
            self::SRO_EXCERPT         => 'Выписка из реестра членов СРО',
            self::NOPRIZ_NOTIFICATION => 'Уведомление НОПРИЗ',
        };
    }

    public static function choices(): array
    {
        $choices = [];
        foreach (self::cases() as $case) {
            $choices[$case->label()] = $case->value;
        }
        return $choices;
    }
}
