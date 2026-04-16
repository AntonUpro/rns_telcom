<?php

declare(strict_types=1);

namespace App\Dto\Calculation\CalculationResult;

/**
 * Payload из фронтенда для сохранения/расчёта всех таблиц.
 */
class CalculationResultDto
{
    /** @param array<string, TablePayloadDto> $tables */
    public function __construct(
        public readonly array $tables,
    ) {
    }

    public static function fromArray(array $data): self
    {
        $tables = [];

        foreach ($data as $key => $tableData) {
            if (!is_array($tableData)) {
                continue;
            }
            $tables[$key] = new TablePayloadDto(
                rows: $tableData['rows'] ?? [],
                enabled: $tableData['enabled'] ?? null,
            );
        }

        return new self($tables);
    }

    public function toPayloadArray(): array
    {
        $result = [];
        foreach ($this->tables as $key => $table) {
            $result[$key] = $table->toArray();
        }

        return $result;
    }
}
