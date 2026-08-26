<?php

declare(strict_types=1);

namespace App\Enum\Calculation;

enum LoadTypeEnum: string
{
    case COMPRESSED = 'compressed';
    case TENSION = 'tension';

    public function label(): string
    {
        return match ($this) {
            self::COMPRESSED => 'Сжатый',
            self::TENSION => 'Растянутый',
        };
    }

    public static function toOptions(): array
    {
        return array_map(
            fn(self $case) => ['value' => $case->value, 'label' => $case->label()],
            self::cases()
        );
    }

    public function isCompress(): bool
    {
        return $this === self::COMPRESSED;
    }
}
