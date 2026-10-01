<?php

declare(strict_types=1);

namespace App\Dto\Calculation\CalculationResult\Row;

use App\Enum\Calculation\TowerWindDirectionEnum;

/**
 * Строка таблицы «Максимальные нагрузки, действующие на фундаменты» (башня):
 * реакции под одним поясом при одном направлении ветра. Все реакции — ввод из ПК, тс.
 */
final readonly class TowerFoundationLoadRowDto
{
    public function __construct(
        public TowerWindDirectionEnum $direction,
        public int $beltNumber,
        public ?float $rz = null,
        public ?float $rx = null,
        public ?float $ry = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            direction: TowerWindDirectionEnum::from($data['direction']),
            beltNumber: (int)$data['beltNumber'],
            rz: is_numeric($data['rz'] ?? null) ? (float)$data['rz'] : null,
            rx: is_numeric($data['rx'] ?? null) ? (float)$data['rx'] : null,
            ry: is_numeric($data['ry'] ?? null) ? (float)$data['ry'] : null,
        );
    }

    public function toArray(): array
    {
        return [
            'direction' => $this->direction->value,
            'beltNumber' => $this->beltNumber,
            'rz' => $this->rz,
            'rx' => $this->rx,
            'ry' => $this->ry,
        ];
    }
}
