<?php

declare(strict_types=1);

namespace App\Service\Calculation\CalculationResult;

use App\Entity\Calculation;
use App\Entity\CalculationResultTable;
use App\Enum\Calculation\BraceConnectionTypeEnum;
use App\Enum\Calculation\FlexibilityTypeEnum;
use App\Enum\Calculation\LoadTypeEnum;
use App\Enum\Calculation\ResultTableTypeEnum;
use App\Enum\Calculation\SchemeNumberEnum;
use App\Enum\Pillar\ElementTypeEnum;
use App\Enum\Pillar\PillarEnum;
use App\Repository\CalculationResultTableRepository;
use App\Service\Calculation\PillarByHeight\SimpleCalculator;
use Doctrine\ORM\EntityManagerInterface;

final class CalculationResultService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly CalculationResultTableRepository $calculationResultTableRepository,
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
        $existing = $this->calculationResultTableRepository->findAllByCalculationIndexed($calculation);

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
                $this->entityManager->persist($entity);
            }

            $entity->setEnabled($enabled);
            $entity->setRows($rows);
        }

        $this->entityManager->flush();
    }

    /**
     * Возвращает все сохранённые таблицы для расчёта в виде массива,
     * сгруппированного по table_type.
     *
     * @return array<string, array{enabled: bool, rows: array}>
     */
    public function getAll(Calculation $calculation): array
    {
        $entities = $this->calculationResultTableRepository->findAllByCalculationIndexed($calculation);

        $result = [];
        foreach ($entities as $key => $entity) {
            $result[$key] = [
                'enabled' => $entity->isEnabled(),
                'rows'    => $entity->getRows(),
            ];
        }

        $result = $this->addDefaultData($calculation, $result);

        return $result;
    }

    /**
     * Возвращает данные одной таблицы или null, если она ещё не сохранялась.
     */
    public function getTable(
        Calculation $calculation,
        ResultTableTypeEnum $tableType,
    ): ?CalculationResultTable {
        return $this->calculationResultTableRepository->findByCalculationAndType($calculation, $tableType);
    }

    /**
     * Удаляет все сохранённые результаты для расчёта.
     */
    public function deleteAll(Calculation $calculation): void
    {
        $entities = $this->calculationResultTableRepository->findByCalculation($calculation);

        foreach ($entities as $entity) {
            $this->entityManager->remove($entity);
        }

        $this->entityManager->flush();
    }

    private function addDefaultData(Calculation $calculation, array &$result): array
    {
        $specificData = $calculation->getCalculationData()->getConcretePillarSpecificData();
        $pillarEnum = $specificData->toEnumPillar();

        if (empty($result[ResultTableTypeEnum::PILLAR_FORCES->value])) {
            $result[ResultTableTypeEnum::PILLAR_FORCES->value] = [
                'enabled' => true,
                'rows'    => $this->buildDefaultPillarForcesRows($pillarEnum, $specificData->pillarHeight),
            ];
        }

//        $result[ResultTableTypeEnum::CRACK_OPENING->value] = [
//            'enabled' => true,
//            'rows'    => [
//                ['mark' => 0, 'pillarType' => $pillarEnum->value, 'crackWidthAllowable' => 0.3],
//            ],
//        ];

        if (empty($result[ResultTableTypeEnum::SUPERSTRUCTURE_STABILITY_BELT->value])) {
            $result[ResultTableTypeEnum::SUPERSTRUCTURE_STABILITY_BELT->value] = [
                'enabled' => false,
                'rows'    => $this->buildDefaultStabilityRows($calculation, ElementTypeEnum::BELT),
            ];
        }

        if (empty($result[ResultTableTypeEnum::SUPERSTRUCTURE_STABILITY_BRACE->value])) {
            $result[ResultTableTypeEnum::SUPERSTRUCTURE_STABILITY_BRACE->value] = [
                'enabled' => false,
                'rows'    => $this->buildDefaultStabilityRows($calculation, ElementTypeEnum::BRACE),
            ];
        }

        return $result;
    }

    private function buildDefaultStabilityRows(Calculation $calculation, ElementTypeEnum $onlyType): array
    {
        $rows = [];

        foreach ($calculation->getPillarPlatform()?->getSortSectionsByNumber() ?? [] as $section) {
            if ($section->isStrut()) {
                continue;
            }

            foreach ($section->getElementsDto() as $element) {
                if ($element->elementType->value !== $onlyType->value) {
                    continue;
                }

                $rows[] = [
                    'sectionNumber' => $section->getNumberSection(),
                    'mark' => $section->getMountHeightTopM(),
                    'element' => $element->elementType->value,
                    'profileType' => $element->sectionConstructType->toGaugeProfile()->value,
                    'elementLength' => $element->getLengthCm(),
                    'loadType' => LoadTypeEnum::COMPRESSED->value,
                    'connectionType' => BraceConnectionTypeEnum::SINGLE_BOLT_OR_GUSSET->value,
                    'schemeNumber' => SchemeNumberEnum::A->value,
                    'flexibility' => $onlyType->value === ElementTypeEnum::BELT->value
                        ? FlexibilityTypeEnum::ONE_A->value
                        : FlexibilityTypeEnum::TWO_A->value,
                    'ry' => 240,
                ];
            }
        }

        return $rows;
    }

    /**
     * Строит строки таблицы «Максимальные усилия в стволе опоры» — по одной
     * на каждую метровую отметку от 0 (земля) до последней перед вершиной столба.
     * Если высота столба ещё не введена, возвращает одну строку на отметке 0.
     *
     * @return array<int, array<string, mixed>>
     */
    private function buildDefaultPillarForcesRows(PillarEnum $pillarEnum, ?float $pillarHeight): array
    {
        $mAllowable = $pillarEnum->getAllowableMomentByStrength();
        $lastMark = $pillarHeight !== null ? SimpleCalculator::lastMarkAboveGround($pillarHeight) : -1;

        if ($lastMark < 0) {
            return [
                ['mark' => 0, 'pillarType' => $pillarEnum->value, 'mAllowable' => $mAllowable],
            ];
        }

        $rows = [];
        for ($mark = 0; $mark <= $lastMark; $mark++) {
            $rows[] = [
                'mark' => $mark,
                'pillarType' => $pillarEnum->value,
                'mCalc' => null,
                'mAllowable' => $mAllowable,
                'kMax' => null,
                'mAllowableManual' => false,
                'sectionDataAvailable' => true,
            ];
        }

        return $rows;
    }
}
