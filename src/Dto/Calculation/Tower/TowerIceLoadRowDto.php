<?php

declare(strict_types=1);

namespace App\Dto\Calculation\Tower;

/**
 * Гололёдная нагрузка на элементы одной секции башни (СП 20.13330.2016, раздел 12).
 */
final readonly class TowerIceLoadRowDto
{
    public function __construct(
        public int $sectionNumber,
        /** Отметка верха секции, м */
        public float $topMark,
        /** Коэффициент k изменения толщины стенки гололёда по высоте (для середины секции) */
        public float $k,
        /** Коэффициент μ2 — доля обледеневающей поверхности */
        public float $mu2,
        /** Толщина стенки гололёда b, мм */
        public float $thickness,
        /** Плотность льда ρ, г/см³ */
        public float $density,
        /** Ускорение свободного падения g, м/с² */
        public float $gravity,
        /** Коэффициент надёжности по нагрузке γf */
        public float $reliabilityCoefficient,
        /** Расчётная линейная гололёдная нагрузка i', Па */
        public float $load,
    ) {
    }
}
