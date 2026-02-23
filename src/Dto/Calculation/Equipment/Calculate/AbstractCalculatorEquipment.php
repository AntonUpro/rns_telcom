<?php

declare(strict_types=1);

namespace App\Dto\Calculation\Equipment\Calculate;

use App\Calculator\CalculatorKLambda;

abstract class AbstractCalculatorEquipment
{
    public abstract function calcArea(): float;

    public abstract function calcAb(): float;

    public abstract function calcLambdaE(): float;


    public function calcKLambda(): float
    {
        return CalculatorKLambda::calc($this->calcLambdaE());
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
