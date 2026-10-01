<?php

declare(strict_types=1);

namespace App\Enum\Calculation;

/**
 * Вид вертикальной нагрузки на фундамент башни при сравнении с проектной.
 */
enum FoundationLoadKindEnum: string
{
    /** Максимальная сжимающая реакция Rz > 0 */
    case PRESSING = 'pressing';
    /** Максимальная по модулю растягивающая реакция Rz < 0 */
    case PULLING = 'pulling';

    public function label(): string
    {
        return match ($this) {
            self::PRESSING => 'Прижимающая',
            self::PULLING => 'Выдергивающая',
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
