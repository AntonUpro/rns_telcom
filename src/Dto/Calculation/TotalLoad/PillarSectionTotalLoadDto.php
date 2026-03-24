<?php

declare(strict_types=1);

namespace App\Dto\Calculation\TotalLoad;

/**
 * Строка таблицы 1: суммарная нагрузка на ствол опоры и коммуникации по секции.
 */
final readonly class PillarSectionTotalLoadDto
{
    public function __construct(
        /** № секции */
        public int $sectionNumber,
        /** Высотная отметка верха секции, мм */
        public float $topHeight,
        /** Высота секции, мм */
        public float $sectionHeight,
        /** Суммарная ветровая нагрузка (ствол + кабели + кабельрост + лестница), Н */
        public float $totalLoad,
        /** Нагрузка на 1 погонный метр, Н/м */
        public float $loadPerLinearMeter,
    ) {
    }

    public function toArray(): array
    {
        return [
            'sectionNumber'      => $this->sectionNumber,
            'topHeight'          => $this->topHeight,
            'sectionHeight'      => $this->sectionHeight,
            'totalLoad'          => round($this->totalLoad, 2),
            'loadPerLinearMeter' => round($this->loadPerLinearMeter, 2),
        ];
    }
}
