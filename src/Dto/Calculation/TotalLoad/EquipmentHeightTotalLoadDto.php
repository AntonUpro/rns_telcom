<?php

declare(strict_types=1);

namespace App\Dto\Calculation\TotalLoad;

/**
 * Строка таблицы 3: суммарная нагрузка на оборудование, сгруппированная по высотной отметке подвеса.
 */
final readonly class EquipmentHeightTotalLoadDto
{
    public function __construct(
        /** Высотная отметка подвеса оборудования, мм */
        public float $heightMark,
        /**
         * Высота — интервал от предыдущей отметки до текущей, мм.
         * Определяет «зону влияния» нагрузки в высоту.
         * TODO: уточнить методику определения высотного интервала у проектировщика.
         */
        public float $height,
        /** Суммарная ветровая нагрузка всего оборудования на данной отметке, кг */
        public float $totalLoad,
    ) {
    }

    public function toArray(): array
    {
        return [
            'heightMark' => $this->heightMark,
            'height'     => $this->height,
            'totalLoad'  => round($this->totalLoad, 2),
        ];
    }
}
