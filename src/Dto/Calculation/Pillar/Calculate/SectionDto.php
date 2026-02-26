<?php

declare(strict_types=1);

namespace App\Dto\Calculation\Pillar\Calculate;

use App\Enum\Pillar\FormConstructEnum;

final readonly class SectionDto
{
    public function __construct(
        public int $number,
        public float $height,
        public float $diameterTop,
        public float $diameterBottom,
        public float $topMark,
        public FormConstructEnum $formConstruct,
    ) {
    }

    /**
     * Расчет среднего диаметра
     *
     * @return float средний диаметр
     */
    public function getAverageDiameter(): float
    {
        return ($this->diameterTop + $this->diameterBottom) / 2;
    }

    /**
     * Считаем площадь контура
     *
     * @return float Площадь в мм^2
     */
    public function getContourArea(): float
    {
        return $this->getAverageDiameter() * $this->height;
    }

    public function middleMark(): float
    {
        return $this->topMark - $this->height / 2;
    }

    public function buttonMark(): float
    {
        return $this->topMark - $this->height;
    }
}
