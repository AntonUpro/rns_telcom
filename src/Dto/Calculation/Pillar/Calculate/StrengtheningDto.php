<?php

declare(strict_types=1);

namespace App\Dto\Calculation\Pillar\Calculate;

use App\Enum\Pillar\FormConstructEnum;

final readonly class StrengtheningDto
{
    public function __construct(
        public float $height,
        public float $width,
        public float $depth,
        public FormConstructEnum $type
    ) {
    }
}
