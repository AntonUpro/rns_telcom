<?php

declare(strict_types=1);

namespace App\Service\DocumentGenerator\Report;

use App\Enum\CalculationTypeEnum;
use App\Exception\NotFoundException;
use App\Repository\CalculationRepository;

/**
 * Выбирает генератор отчёта ОТС по типу расчёта: башня или столб.
 */
final readonly class OtsReportDispatcher
{
    public function __construct(
        private CalculationRepository $calculationRepository,
        private OtsReportGenerator $pillarReportGenerator,
        private TowerReportGenerator $towerReportGenerator,
    ) {
    }

    /**
     * @return string Абсолютный путь к созданному файлу
     *
     * @throws NotFoundException  если расчёт не найден
     * @throws \RuntimeException  если не удалось сохранить файл
     */
    public function generate(int $calculationId, string $outputDir): string
    {
        $calculation = $this->calculationRepository->findById($calculationId);
        if ($calculation === null) {
            throw new NotFoundException(sprintf('Расчёт #%d не найден', $calculationId));
        }

        return $calculation->getType() === CalculationTypeEnum::TOWER
            ? $this->towerReportGenerator->generate($calculationId, $outputDir)
            : $this->pillarReportGenerator->generate($calculationId, $outputDir);
    }
}
