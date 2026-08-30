<?php

declare(strict_types=1);

namespace App\Service\DocumentGenerator\Report;

use App\Entity\AppendixStaticImage;
use App\Entity\Calculation;
use App\Enum\AppendixTypeEnum;
use App\Entity\CalculationData;
use App\Entity\CalculationDocument;
use App\Entity\CalculationImage;
use App\Entity\CalculationResultTable;
use App\Enum\Calculation\ResultTableTypeEnum;

final class ReportContext
{
    /**
     * @param CalculationDocument[] $documents
     * @param array<string, CalculationResultTable> $resultTables keyed by ResultTableTypeEnum::value
     * @param CalculationImage[] $calculationImages
     * @param array<string, AppendixStaticImage[]> $appendixImages keyed by AppendixTypeEnum::value
     * @param string|null $chiefEngineerSignaturePath абсолютный путь к файлу подписи ГИП
     * @param string|null $engineerSignaturePath абсолютный путь к файлу подписи инженера-проектировщика
     * @param string|null $engineerShortName имя инженера в формате «Фамилия И.О.»
     */
    public function __construct(
        public readonly Calculation $calculation,
        public readonly array $documents,
        public readonly array $resultTables,
        public readonly array $calculationImages,
        public readonly array $appendixImages = [],
        public readonly ?string $chiefEngineerSignaturePath = null,
        public readonly ?string $engineerSignaturePath = null,
    ) {
    }

    public function getData(): ?CalculationData
    {
        return $this->calculation->getCalculationData();
    }

    public function getAddress(): string
    {
        $d = $this->getData();
        if ($d === null) {
            return '—';
        }
        return implode(', ', array_filter([$d->getRegion(), $d->getLocality()])) ?: '—';
    }

    public function getHeightM(): string
    {
        return $this->getData()?->getAmsHeight() ?? '?';
    }

    public function getResultTable(ResultTableTypeEnum $type): ?CalculationResultTable
    {
        return $this->resultTables[$type->value] ?? null;
    }

    /** @return AppendixStaticImage[] */
    public function getAppendixImages(AppendixTypeEnum $type): array
    {
        return $this->appendixImages[$type->value] ?? [];
    }

    public function getCalculationImageByType(string $type): ?CalculationImage
    {
        foreach ($this->calculationImages as $calculationImage) {
            if ($calculationImage->getImageType() === $type) {
                return $calculationImage;
            }
        }

        return null;
    }

    /** @return CalculationImage[] */
    public function getCalculationImagesByType(string $type): array
    {
        return array_values(array_filter(
            $this->calculationImages,
            fn(CalculationImage $img) => $img->getImageType() === $type,
        ));
    }

    public function hasAnyExceededKuse(): bool
    {
        foreach ($this->resultTables as $table) {
            if (! $table->isEnabled()) {
                continue;
            }
            foreach ($table->getRows() as $row) {
                foreach (['kMax', 'kUse', 'kUseStability', 'kUseDeformation'] as $field) {
                    if (isset($row[$field]) && $row[$field] !== null && (float)$row[$field] > 1.0) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    public function getPillarForcesMaxKuse(): ?float
    {
        $table = $this->getResultTable(ResultTableTypeEnum::PILLAR_FORCES);
        if ($table === null) {
            return null;
        }
        $max = null;
        foreach ($table->getRows() as $row) {
            $k = isset($row['kMax']) ? (float)$row['kMax'] : null;
            if ($k !== null && ($max === null || $k > $max)) {
                $max = $k;
            }
        }
        return $max;
    }

    public function getDeformationMaxKuse(): ?float
    {
        $table = $this->getResultTable(ResultTableTypeEnum::DEFORMATION);
        if ($table === null || ! $table->isEnabled()) {
            return null;
        }
        $max = null;
        foreach ($table->getRows() as $row) {
            $k = isset($row['kUse']) ? (float)$row['kUse'] : null;
            if ($k !== null && ($max === null || $k > $max)) {
                $max = $k;
            }
        }
        return $max;
    }

    public function getPlatformForcesMaxKuse(): ?float
    {
        $table = $this->getResultTable(ResultTableTypeEnum::PLATFORM_FORCES);
        if ($table === null || ! $table->isEnabled()) {
            return null;
        }

        return $this->getMaxKUse($table->getRows());
    }

    public function getSuperstructureStressMaxKuse(): ?float
    {
        $table = $this->getResultTable(ResultTableTypeEnum::SUPERSTRUCTURE_STRESS);
        if ($table === null || ! $table->isEnabled()) {
            return null;
        }

        return $this->getMaxKUse($table->getRows());
    }

    public function getBraceStressMaxKuse(): ?float
    {
        $table = $this->getResultTable(ResultTableTypeEnum::BRACE_STRESS);
        if ($table === null || ! $table->isEnabled()) {
            return null;
        }

        return $this->getMaxKUse($table->getRows());
    }

    public function getSuperstructureStabilityBeltMaxKuse(): ?float
    {
        $table = $this->getResultTable(ResultTableTypeEnum::SUPERSTRUCTURE_STABILITY_BELT);
        if ($table === null || ! $table->isEnabled()) {
            return null;
        }

        return $this->getMaxKUse($table->getRows());
    }

    public function getSuperstructureStabilityBraceMaxKuse(): ?float
    {
        $table = $this->getResultTable(ResultTableTypeEnum::SUPERSTRUCTURE_STABILITY_BRACE);
        if ($table === null || ! $table->isEnabled()) {
            return null;
        }

        return $this->getMaxKUse($table->getRows());
    }

    public function getFoundationMaxKuse(): ?float
    {
        $table = $this->getResultTable(ResultTableTypeEnum::FOUNDATION);
        if ($table === null || ! $table->isEnabled()) {
            return null;
        }
        $kUseStabilityMax = null;
        $kUseDeformationMax = null;
        foreach ($table->getRows() as $row) {
            $kUseStability = isset($row['kUseStability']) ? (float)$row['kUseStability'] : null;
            if ($kUseStability !== null && ($kUseStabilityMax === null || $kUseStability > $kUseStabilityMax)) {
                $kUseStabilityMax = $kUseStability;
            }
            $kUseDeformation = isset($row['kUseDeformation']) ? (float)$row['kUseDeformation'] : null;
            if ($kUseDeformation !== null && ($kUseDeformation === null || $kUseDeformation > $kUseDeformationMax)) {
                $kUseDeformationMax = $kUseDeformation;
            }
        }

        return max($kUseStabilityMax, $kUseDeformationMax);
    }

    public function getMaxK(): ?float
    {
        $ks = [
            ResultTableTypeEnum::PILLAR_FORCES->value => $this->getPillarForcesMaxKuse(),
            ResultTableTypeEnum::PLATFORM_FORCES->value => $this->getPlatformForcesMaxKuse(),
            ResultTableTypeEnum::SUPERSTRUCTURE_STRESS->value => $this->getSuperstructureStressMaxKuse(),
            ResultTableTypeEnum::SUPERSTRUCTURE_STABILITY_BELT->value => $this->getSuperstructureStabilityBeltMaxKuse(),
            ResultTableTypeEnum::SUPERSTRUCTURE_STABILITY_BRACE->value => $this->getSuperstructureStabilityBraceMaxKuse(),
            ResultTableTypeEnum::BRACE_STRESS->value => $this->getBraceStressMaxKuse(),
            ResultTableTypeEnum::FOUNDATION->value => $this->getFoundationMaxKuse(),
        ];
        $max = null;
        foreach ($ks as $k) {
            if ($k !== null && ($max === null || $k > $max)) {
                $max = $k;
            }
        }

        return $max;
    }

    /**
     * @return ResultTableTypeEnum[]
     */
    public function getNegativeCalculations(): array
    {
        $ks = [
            ResultTableTypeEnum::PILLAR_FORCES->value => $this->getPillarForcesMaxKuse(),
            ResultTableTypeEnum::PLATFORM_FORCES->value => $this->getPlatformForcesMaxKuse(),
            ResultTableTypeEnum::SUPERSTRUCTURE_STRESS->value => $this->getSuperstructureStressMaxKuse(),
            ResultTableTypeEnum::SUPERSTRUCTURE_STABILITY_BELT->value => $this->getSuperstructureStabilityBeltMaxKuse(),
            ResultTableTypeEnum::SUPERSTRUCTURE_STABILITY_BRACE->value => $this->getSuperstructureStabilityBraceMaxKuse(),
            ResultTableTypeEnum::BRACE_STRESS->value => $this->getBraceStressMaxKuse(),
            ResultTableTypeEnum::FOUNDATION->value => $this->getFoundationMaxKuse(),
        ];

        $response = [];
        foreach ($ks as $typeCalculation => $calculation) {
            if ($calculation !== null && $calculation >= 1.0) {
                $response[] = ResultTableTypeEnum::from($typeCalculation);
            }
        }
        return $response;
    }

    private function getMaxKUse(array $rows): ?float
    {
        $max = null;
        foreach ($rows as $row) {
            $k = isset($row['kUse']) ? (float)$row['kUse'] : null;
            if ($k !== null && ($max === null || $k > $max)) {
                $max = $k;
            }
        }
        return $max;
    }
}
