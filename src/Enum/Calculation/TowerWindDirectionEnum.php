<?php

declare(strict_types=1);

namespace App\Enum\Calculation;

/**
 * Направления ветра, для которых из ПК снимаются реакции опор башни.
 */
enum TowerWindDirectionEnum: string
{
    case W1 = 'w1';
    case W2 = 'w2';
    case W3 = 'w3';

    public function label(): string
    {
        return match ($this) {
            self::W1 => 'W1 (на грань)',
            self::W2 => 'W2 (на ребро)',
            self::W3 => 'W3 (вдоль грани)',
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
