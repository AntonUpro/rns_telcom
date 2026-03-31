<?php

declare(strict_types=1);

namespace App\Service\Calculation\PillarPlatform\Calculator;

class SectionPermeabilityCalculator
{
    /**
     * ϕ - коэффициент проницаемости → η при 4-х сторонах
     * Линейная интерполяция по таблице:
     * ϕ:  0,1 → η: 0,99
     * ϕ:  0,2 → η: 0,81
     * ϕ:  0,3 → η: 0,65
     * ϕ:  0,4 → η: 0,48
     * ϕ:  0,5 → η: 0,32
     * ϕ:  0,6 → η: 0,15
     */
    public static function calculate(float $fi): float
    {
        $fi = $fi * 10;
        $table = [
            1 => 0.99,
            2 => 0.81,
            3 => 0.65,
            4 => 0.48,
            5 => 0.32,
            6 => 0.15,
        ];

        $keys = array_keys($table);

        if ($fi <= $keys[0]) {
            return $table[$keys[0]];
        }

        $lastKey = $keys[count($keys) - 1];
        if ($fi >= $lastKey) {
            return $table[$lastKey];
        }

        foreach ($keys as $i => $x0) {
            $x1 = $keys[$i + 1];
            if ($fi >= $x0 && $fi <= $x1) {
                $y0 = $table[$x0];
                $y1 = $table[$x1];

                return $y0 + ($fi - $x0) * ($y1 - $y0) / ($x1 - $x0);
            }
        }

        return $table[$lastKey];
    }
}
