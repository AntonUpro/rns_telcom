<?php

declare(strict_types=1);

namespace App\Dto\Calculation\TotalLoad;

/**
 * Строка таблицы 2: ветровая нагрузка на площадку / надстройку / подкосы.
 */
final readonly class PlatformSectionTotalLoadDto
{
    public function __construct(
        /** Метка строки: номер секции или "Подкосы" */
        public string $label,
        /** true — строка соответствует подкосам, а не секции площадки */
        public bool $isStrut,
        /** Высотная отметка верха, мм */
        public float $topHeight,
        /** Высота секции / подкосов, мм */
        public float $height,
        /** Суммарная ветровая нагрузка на секцию, кг */
        public float $totalLoad,
        /** Нагрузка на 1 погонный метр 1 пояса, кг/(м·пояс) */
        public float $loadPerLinearMeterPerBelt,
    ) {
    }

    public function toArray(): array
    {
        return [
            'label'                      => $this->label,
            'isStrut'                    => $this->isStrut,
            'topHeight'                  => $this->topHeight,
            'height'                     => $this->height,
            'totalLoad'                  => round($this->totalLoad, 2),
            'loadPerLinearMeterPerBelt'  => round($this->loadPerLinearMeterPerBelt, 2),
        ];
    }
}
