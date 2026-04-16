<?php

declare(strict_types=1);

namespace App\Service\Calculation\CalculationResult;

use App\Entity\Calculation;
use App\Entity\CalculationResultTable;
use App\Enum\Calculation\ResultTableTypeEnum;
use App\Repository\CalculationResultTableRepository;
use Doctrine\ORM\EntityManagerInterface;

final class CalculationResultService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly CalculationResultTableRepository $repository,
    ) {
    }

    /**
     * Сохраняет (upsert) все переданные таблицы для расчёта.
     *
     * Ожидаемый формат $payload:
     * [
     *   'table1' => ['rows' => [...]],
     *   'table2' => ['rows' => [...]],
     *   'table3' => ['enabled' => true,  'rows' => [...]],
     *   ...
     * ]
     */
    public function saveAll(Calculation $calculation, array $payload): void
    {
        $existing = $this->repository->findAllByCalculationIndexed($calculation);

        foreach (ResultTableTypeEnum::cases() as $type) {
            $key = $type->value;

            if (!array_key_exists($key, $payload)) {
                continue;
            }

            $data    = $payload[$key];
            $rows    = $data['rows'] ?? [];
            $enabled = $type->isOptional() ? (bool) ($data['enabled'] ?? false) : true;

            $entity = $existing[$key] ?? null;

            if ($entity === null) {
                $entity = new CalculationResultTable($calculation, $type);
                $this->em->persist($entity);
            }

            $entity->setEnabled($enabled);
            $entity->setRows($rows);
        }

        $this->em->flush();
    }

    /**
     * Возвращает все сохранённые таблицы для расчёта в виде массива,
     * сгруппированного по table_type.
     *
     * @return array<string, array{enabled: bool, rows: array}>
     */
    public function getAll(Calculation $calculation): array
    {
        $entities = $this->repository->findAllByCalculationIndexed($calculation);

        $result = [];
        foreach ($entities as $key => $entity) {
            $result[$key] = [
                'enabled' => $entity->isEnabled(),
                'rows'    => $entity->getRows(),
            ];
        }

        return $result;
    }

    /**
     * Возвращает данные одной таблицы или null, если она ещё не сохранялась.
     */
    public function getTable(
        Calculation $calculation,
        ResultTableTypeEnum $tableType,
    ): ?CalculationResultTable {
        return $this->repository->findByCalculationAndType($calculation, $tableType);
    }

    /**
     * Удаляет все сохранённые результаты для расчёта.
     */
    public function deleteAll(Calculation $calculation): void
    {
        $entities = $this->repository->findByCalculation($calculation);

        foreach ($entities as $entity) {
            $this->em->remove($entity);
        }

        $this->em->flush();
    }
}
