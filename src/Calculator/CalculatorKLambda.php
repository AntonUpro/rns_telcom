<?php

declare(strict_types=1);

namespace App\Calculator;

class CalculatorKLambda
{
    public static function calc(float $lambdaE): float
    {
        // Таблица данных из СП 20.13330.2016 рисунок В.23
        $table = [
            1 => 0.6,
            2 => 0.628,
            3 => 0.646,
            4 => 0.659,
            5 => 0.666,
            6 => 0.675,
            7 => 0.684,
            8 => 0.689,
            9 => 0.694,
            10 => 0.7,
            20 => 0.776,
            30 => 0.816,
            40 => 0.853,
            50 => 0.875,
            60 => 0.9,
            70 => 0.912,
            80 => 0.926,
            90 => 0.939,
            100 => 0.95,
            200 => 1,
        ];

        $values = array_keys($table);
        $minValue = $values[0];
        $maxValue = end($values);

        // Случай для значений = 1
        if ($lambdaE <= $minValue) {
            return $table[$minValue];
        }

        // Случай для значений >= 200
        if ($lambdaE >= $maxValue) {
            return $table[$maxValue];
        }

        // Поиск интервала для интерполяции
        $index = 0;
        while ($index < count($values) - 1 && $values[$index + 1] < $lambdaE) {
            $index++;
        }

        $v1 = $values[$index];
        $v2 = $values[$index + 1];
        $k1 = $table[$v1];
        $k2 = $table[$v2];

        // Линейная интерполяция
        return $k1 + ($k2 - $k1) * ($lambdaE - $v1) / ($v2 - $v1);
    }
}
