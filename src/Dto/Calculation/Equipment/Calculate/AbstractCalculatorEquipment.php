<?php

declare(strict_types=1);

namespace App\Dto\Calculation\Equipment\Calculate;

abstract class AbstractCalculatorEquipment
{
    public abstract function calcArea(): float;

    public abstract function calcAb(): float;

    public abstract function calcLambdaE(): float;


    public function calcKLambda(): float
    {
        $lambdaE = $this->calcLambdaE();

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

    public function calcCXInf(): float
    {
        $ab = $this->calcAb() * 10;

        // Таблица данных из СП 20.13330.2016 рисунок В.23
        $table = [
            2 => 2,
            7 => 2.4,
            10 => 2.1,
            20 => 1.65,
            50 => 1,
            100 => 0.9,
        ];

        $values = array_keys($table);
        $minValue = $values[0];
        $maxValue = end($values);

        // Случай для значений менее 0.2
        if ($ab <= $minValue) {
            return $table[$minValue];
        }

        // Случай для значений >= 10
        if ($ab >= $maxValue) {
            return $table[$maxValue];
        }

        // Поиск интервала для интерполяции
        $index = 0;
        while ($index < count($values) - 1 && $values[$index + 1] < $ab) {
            $index++;
        }

        $v1 = $values[$index];
        $v2 = $values[$index + 1];
        $k1 = $table[$v1];
        $k2 = $table[$v2];

        // Линейная интерполяция
        return $k1 + ($k2 - $k1) * ($ab - $v1) / ($v2 - $v1);
    }

    public function calcCX(): float
    {
        $kLambda = $this->calcKLambda();
        $cxInf = $this->calcCXInf();

        return $kLambda * $cxInf;
    }
}
