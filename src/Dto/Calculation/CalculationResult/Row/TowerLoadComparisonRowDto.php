<?php

declare(strict_types=1);

namespace App\Dto\Calculation\CalculationResult\Row;

use App\Enum\Calculation\FoundationLoadKindEnum;

/**
 * Строка таблицы «Сравнение расчетных нагрузок с проектными» (башня).
 *
 * Расчётные значения берутся из таблицы нагрузок на фундаменты,
 * проектные — ввод пользователя, КИ = расчётная / проектная. Все силы в тс.
 */
final readonly class TowerLoadComparisonRowDto
{
    public function __construct(
        public FoundationLoadKindEnum $loadKind,
        /** Проектная вертикальная нагрузка — ввод */
        public ?float $projectVertical = null,
        /** Проектная сдвигающая нагрузка — ввод */
        public ?float $projectShear = null,
        /** Расчётная вертикальная нагрузка (|Rz|) — вычисл. */
        public ?float $calcVertical = null,
        /** Расчётная сдвигающая нагрузка (|Rx| той же строки) — вычисл. */
        public ?float $calcShear = null,
        public ?float $kUseVertical = null,
        public ?float $kUseShear = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            loadKind: FoundationLoadKindEnum::from($data['loadKind']),
            projectVertical: is_numeric($data['projectVertical'] ?? null) ? (float)$data['projectVertical'] : null,
            projectShear: is_numeric($data['projectShear'] ?? null) ? (float)$data['projectShear'] : null,
        );
    }

    public function withComputed(?float $calcVertical, ?float $calcShear): self
    {
        return new self(
            loadKind: $this->loadKind,
            projectVertical: $this->projectVertical,
            projectShear: $this->projectShear,
            calcVertical: $calcVertical,
            calcShear: $calcShear,
            kUseVertical: self::ratio($calcVertical, $this->projectVertical),
            kUseShear: self::ratio($calcShear, $this->projectShear),
        );
    }

    public function toArray(): array
    {
        return [
            'loadKind' => $this->loadKind->value,
            'projectVertical' => $this->projectVertical,
            'projectShear' => $this->projectShear,
            'calcVertical' => $this->calcVertical,
            'calcShear' => $this->calcShear,
            'kUseVertical' => $this->kUseVertical,
            'kUseShear' => $this->kUseShear,
        ];
    }

    private static function ratio(?float $calc, ?float $project): ?float
    {
        if ($calc === null || $project === null || $project <= 0.0) {
            return null;
        }

        return round($calc / $project, 4);
    }
}
