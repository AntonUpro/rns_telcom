<?php

declare(strict_types=1);

namespace App\Service\DocumentGenerator\Report;

use App\Exception\NotFoundException;
use App\Repository\AppendixStaticImageRepository;
use App\Repository\CalculationDocumentRepository;
use App\Repository\CalculationImageRepository;
use App\Repository\CalculationRepository;
use App\Repository\CalculationResultTableRepository;

/**
 * Собирает {@see ReportContext} — все данные расчёта, нужные разделам отчёта.
 */
final readonly class ReportContextFactory
{
    public function __construct(
        private CalculationRepository $calculationRepository,
        private CalculationDocumentRepository $documentRepository,
        private CalculationImageRepository $imageRepository,
        private CalculationResultTableRepository $resultTableRepository,
        private AppendixStaticImageRepository $appendixImageRepository,
        private string $projectDir,
    ) {
    }

    /**
     * @throws NotFoundException если расчёт не найден
     */
    public function create(int $calculationId): ReportContext
    {
        $calculation = $this->calculationRepository->findById($calculationId);
        if ($calculation === null) {
            throw new NotFoundException(sprintf('Расчёт #%d не найден', $calculationId));
        }

        $chiefSignaturePath = $this->projectDir . '/static_image/sign_DA.png';

        $engineer = $calculation->getUser();
        $engineerSignaturePath = null;
        if ($engineer?->getSignatureFileName() !== null) {
            $engineerSignaturePath = $this->projectDir . '/var/uploads/signatures/' . $engineer->getSignatureFileName();
            if (! file_exists($engineerSignaturePath)) {
                $engineerSignaturePath = null;
            }
        }

        return new ReportContext(
            calculation: $calculation,
            documents: $this->documentRepository->findByCalculation($calculationId),
            resultTables: $this->resultTableRepository->findAllByCalculationIndexed($calculation),
            calculationImages: $this->imageRepository->findByCalculation($calculationId),
            appendixImages: $this->appendixImageRepository->findAllGroupedByType(),
            chiefEngineerSignaturePath: file_exists($chiefSignaturePath) ? $chiefSignaturePath : null,
            engineerSignaturePath: $engineerSignaturePath,
        );
    }
}
