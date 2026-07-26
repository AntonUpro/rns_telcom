<?php

declare(strict_types=1);

namespace App\Service\Calculation\CalculationResult\Calculator;

use App\Entity\Calculation;

interface TableCalculatorInterface
{
    /**
     * Принимает массив строк таблицы в сыром виде (из JSON-запроса),
     * вычисляет computed-поля и возвращает обновлённые строки.
     *
     * @param array<int, array<string, mixed>> $rawRows
     * @return array<int, array<string, mixed>>
     */
    public function calculateRows(array $rawRows, ?Calculation $calculation = null): array;
}
