<?php

declare(strict_types=1);

namespace App\Service\Calculation\Document;

use App\Entity\CalculationDocument;
use App\Repository\CalculationDocumentRepository;
use App\Repository\CalculationRepository;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;

class CalculationDocumentService
{
    public function __construct(
        private readonly CalculationDocumentRepository $documentRepository,
        private readonly CalculationRepository         $calculationRepository,
        private readonly EntityManagerInterface        $entityManager,
    ) {
    }

    /**
     * @return CalculationDocument[]
     */
    public function getDocuments(int $calculationId): array
    {
        return $this->documentRepository->findByCalculation($calculationId);
    }

    /**
     * Полностью заменяет набор документов расчёта.
     *
     * @param array<int, string> $names Список названий документов (порядок = sort_order)
     *
     * @return CalculationDocument[]
     */
    public function saveDocuments(int $calculationId, array $names): array
    {
        $calculation = $this->calculationRepository->find($calculationId);
        if ($calculation === null) {
            throw new InvalidArgumentException(sprintf('Расчет с id=%d не найден', $calculationId));
        }

        // Удаляем существующие записи и сохраняем новые в одной транзакции
        $this->entityManager->wrapInTransaction(function () use ($calculation, $names): void {
            $this->documentRepository->deleteByCalculation($calculation->getId());

            foreach ($names as $index => $name) {
                $name = trim($name);
                if ($name === '') {
                    continue;
                }

                $document = new CalculationDocument();
                $document->setCalculation($calculation);
                $document->setName($name);
                $document->setSortOrder($index);

                $this->entityManager->persist($document);
            }

            $this->entityManager->flush();
        });

        return $this->documentRepository->findByCalculation($calculationId);
    }

    /**
     * @param CalculationDocument[] $documents
     *
     * @return array<int, array>
     */
    public function documentsToArray(array $documents): array
    {
        return array_map(static fn(CalculationDocument $doc) => $doc->toArray(), $documents);
    }
}
