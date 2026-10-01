<?php

declare(strict_types=1);

namespace App\Dto\Calculation\CalculationResult\Row;

/**
 * Строка таблицы «Перемещения верхних узлов опоры от нормативных нагрузок» (башня).
 */
final readonly class TowerDeformationRowDto
{
    public function __construct(
        /** Линейное перемещение верхних узлов, мм — ввод из ПК */
        public ?float $displacement,
        /** Угловое перемещение верхних узлов относительно оси Y, град — ввод из ПК */
        public ?float $angleY,
        /** Угловое перемещение верхних узлов относительно оси Z, град — ввод из ПК */
        public ?float $angleZ,
        /** Высота сооружения, м — из исходных данных башни */
        public ?float $height = null,
        /** Допустимое линейное перемещение, мм — H / 100 */
        public ?float $displacementAllowable = null,
        /** Коэффициент использования — перемещение / допустимое */
        public ?float $kUse = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            displacement: self::toFloat($data['displacement'] ?? null),
            angleY: self::toFloat($data['angleY'] ?? null),
            angleZ: self::toFloat($data['angleZ'] ?? null),
            height: self::toFloat($data['height'] ?? null),
            displacementAllowable: self::toFloat($data['displacementAllowable'] ?? null),
            kUse: self::toFloat($data['kUse'] ?? null),
        );
    }

    public function withComputed(?float $height, ?float $displacementAllowable, ?float $kUse): self
    {
        return new self(
            displacement: $this->displacement,
            angleY: $this->angleY,
            angleZ: $this->angleZ,
            height: $height,
            displacementAllowable: $displacementAllowable,
            kUse: $kUse,
        );
    }

    public function toArray(): array
    {
        return [
            'displacement' => $this->displacement,
            'angleY' => $this->angleY,
            'angleZ' => $this->angleZ,
            'height' => $this->height,
            'displacementAllowable' => $this->displacementAllowable,
            'kUse' => $this->kUse,
        ];
    }

    private static function toFloat(mixed $value): ?float
    {
        return is_numeric($value) ? (float)$value : null;
    }
}
