<?php

declare(strict_types=1);

namespace App\Dto\Calculation\CalculationResult\Row;

use App\Enum\Calculation\BoltDiameterEnum;
use App\Enum\Calculation\BoltStrengthClassEnum;

/**
 * Строка таблицы «Напряжения в фланцевых болтах» (башня).
 */
final readonly class TowerFlangeBoltRowDto
{
    public function __construct(
        /** Номер стыка */
        public ?int $jointNumber,
        /** Отметка стыка, м — по умолчанию верх секции */
        public ?float $mark,
        /** Количество болтов, шт */
        public ?int $boltCount,
        /** Максимальная нагрузка, тс */
        public ?float $maxLoad,
        public ?BoltDiameterEnum $diameter,
        public ?BoltStrengthClassEnum $strengthClass,
        /** Площадь нетто Abn, см² — по диаметру */
        public ?float $netArea = null,
        /** Расчётное напряжение σ, Н/мм² */
        public ?float $sigma = null,
        /** Расчётное сопротивление растяжению Rbp, Н/мм² — по классу прочности */
        public ?float $rbp = null,
        /** Коэффициент использования — σ / Rbp */
        public ?float $kUse = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            jointNumber: is_numeric($data['jointNumber'] ?? null) ? (int)$data['jointNumber'] : null,
            mark: self::toFloat($data['mark'] ?? null),
            boltCount: is_numeric($data['boltCount'] ?? null) ? (int)$data['boltCount'] : null,
            maxLoad: self::toFloat($data['maxLoad'] ?? null),
            diameter: is_numeric($data['diameter'] ?? null) ? BoltDiameterEnum::tryFrom((int)$data['diameter']) : null,
            strengthClass: is_string($data['strengthClass'] ?? null) ? BoltStrengthClassEnum::tryFrom($data['strengthClass']) : null,
            netArea: self::toFloat($data['netArea'] ?? null),
            sigma: self::toFloat($data['sigma'] ?? null),
            rbp: self::toFloat($data['rbp'] ?? null),
            kUse: self::toFloat($data['kUse'] ?? null),
        );
    }

    public function withComputed(?float $netArea, ?float $sigma, ?float $rbp, ?float $kUse): self
    {
        return new self(
            jointNumber: $this->jointNumber,
            mark: $this->mark,
            boltCount: $this->boltCount,
            maxLoad: $this->maxLoad,
            diameter: $this->diameter,
            strengthClass: $this->strengthClass,
            netArea: $netArea,
            sigma: $sigma,
            rbp: $rbp,
            kUse: $kUse,
        );
    }

    public function toArray(): array
    {
        return [
            'jointNumber' => $this->jointNumber,
            'mark' => $this->mark,
            'boltCount' => $this->boltCount,
            'maxLoad' => $this->maxLoad,
            'diameter' => $this->diameter?->value,
            'strengthClass' => $this->strengthClass?->value,
            'netArea' => $this->netArea,
            'sigma' => $this->sigma,
            'rbp' => $this->rbp,
            'kUse' => $this->kUse,
        ];
    }

    private static function toFloat(mixed $value): ?float
    {
        return is_numeric($value) ? (float)$value : null;
    }
}
