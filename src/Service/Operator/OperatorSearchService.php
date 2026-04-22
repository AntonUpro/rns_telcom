<?php

declare(strict_types=1);

namespace App\Service\Operator;

use App\Entity\Operator;
use App\Repository\OperatorRepository;

final readonly class OperatorSearchService
{
    public function __construct(
        private OperatorRepository $operatorRepository,
    ) {
    }

    public function search(string $query): array
    {
        $results = $this->operatorRepository->searchByName($query);

        return array_map(
            static fn(Operator $op) => ['id' => $op->getId(), 'name' => $op->getName()],
            $results,
        );
    }
}
