<?php

declare(strict_types=1);

namespace App\Dto\Calculation\CalculationResult;

class TablePayloadDto
{
    public function __construct(
        public readonly array $rows,
        public readonly ?bool $enabled = null,
    ) {
    }

    public function toArray(): array
    {
        $data = ['rows' => $this->rows];

        if ($this->enabled !== null) {
            $data['enabled'] = $this->enabled;
        }

        return $data;
    }
}
