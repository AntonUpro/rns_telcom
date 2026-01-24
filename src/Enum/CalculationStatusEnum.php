<?php

namespace App\Enum;

enum CalculationStatusEnum: string
{
    case DRAFT = 'draft';
    case CALCULATED = 'calculated';
    case APPROVED = 'approved';
    case ARCHIVED = 'archived';

    public function label(): string
    {
        return match($this) {
            self::DRAFT => 'Черновик',
            self::CALCULATED => 'Рассчитано',
            self::APPROVED => 'Утверждено',
            self::ARCHIVED => 'Архив',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::DRAFT => 'secondary',
            self::CALCULATED => 'info',
            self::APPROVED => 'success',
            self::ARCHIVED => 'warning',
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
