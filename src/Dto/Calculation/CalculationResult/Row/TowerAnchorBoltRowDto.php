<?php

declare(strict_types=1);

namespace App\Dto\Calculation\CalculationResult\Row;

use App\Enum\Calculation\BoltDiameterEnum;
use App\Enum\Calculation\AnchorBoltSteelEnum;

/**
 * Строка таблицы «Напряжения в анкерных болтах» (башня).
 */
final readonly class TowerAnchorBoltRowDto
{
    public const DEFAULT_K0 = 1.18;

    public function __construct(
        /** Количество болтов, шт */
        public ?int $boltCount,
        /** Максимальная нагрузка, тс */
        public ?float $maxLoad,
        public ?BoltDiameterEnum $diameter,
        public ?AnchorBoltSteelEnum $steel,
        /** Коэффициент k0 */
        public ?float $k0 = self::DEFAULT_K0,
        /** Площадь нетто Abn, см² — по диаметру */
        public ?float $netArea = null,
        /** Расчётное напряжение σ, Н/мм² */
        public ?float $sigma = null,
        /** Расчётное сопротивление Rbt, Н/мм² — по стали и диаметру */
        public ?float $rbt = null,
        /** Коэффициент использования — σ / Rbt */
        public ?float $kUse = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            boltCount: is_numeric($data['boltCount'] ?? null) ? (int)$data['boltCount'] : null,
            maxLoad: self::toFloat($data['maxLoad'] ?? null),
            diameter: is_numeric($data['diameter'] ?? null) ? BoltDiameterEnum::tryFrom((int)$data['diameter']) : null,
            steel: is_string($data['steel'] ?? null) ? AnchorBoltSteelEnum::tryFrom($data['steel']) : null,
            k0: array_key_exists('k0', $data) ? self::toFloat($data['k0']) : self::DEFAULT_K0,
            netArea: self::toFloat($data['netArea'] ?? null),
            sigma: self::toFloat($data['sigma'] ?? null),
            rbt: self::toFloat($data['rbt'] ?? null),
            kUse: self::toFloat($data['kUse'] ?? null),
        );
    }

    public function withComputed(?float $netArea, ?float $sigma, ?float $rbt, ?float $kUse): self
    {
        return new self(
            boltCount: $this->boltCount,
            maxLoad: $this->maxLoad,
            diameter: $this->diameter,
            steel: $this->steel,
            k0: $this->k0,
            netArea: $netArea,
            sigma: $sigma,
            rbt: $rbt,
            kUse: $kUse,
        );
    }

    public function toArray(): array
    {
        return [
            'boltCount' => $this->boltCount,
            'maxLoad' => $this->maxLoad,
            'diameter' => $this->diameter?->value,
            'steel' => $this->steel?->value,
            'k0' => $this->k0,
            'netArea' => $this->netArea,
            'sigma' => $this->sigma,
            'rbt' => $this->rbt,
            'kUse' => $this->kUse,
        ];
    }

    private static function toFloat(mixed $value): ?float
    {
        return is_numeric($value) ? (float)$value : null;
    }
}
