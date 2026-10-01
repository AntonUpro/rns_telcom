<?php

declare(strict_types=1);

namespace App\Service\Calculation\CalculationResult;

use App\Entity\Calculation;
use App\Dto\Calculation\CalculationResult\Row\TowerAnchorBoltRowDto;
use App\Entity\CalculationResultTable;
use App\Entity\PlatformSection;
use App\Enum\Calculation\BraceConnectionTypeEnum;
use App\Enum\Calculation\FlexibilityTypeEnum;
use App\Enum\Calculation\FoundationLoadKindEnum;
use App\Enum\Calculation\LoadTypeEnum;
use App\Enum\Calculation\ResultTableTypeEnum;
use App\Enum\Calculation\SchemeNumberEnum;
use App\Enum\Calculation\TowerWindDirectionEnum;
use App\Enum\CalculationTypeEnum;
use App\Enum\Pillar\ElementTypeEnum;
use App\Enum\Pillar\PillarEnum;
use App\Repository\CalculationResultTableRepository;
use App\Service\Calculation\CalculationResult\Calculator\TowerDeformationCalculator;
use App\Service\Calculation\PillarByHeight\SimpleCalculator;
use Doctrine\ORM\EntityManagerInterface;

final class CalculationResultService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly CalculationResultTableRepository $calculationResultTableRepository,
        private readonly TowerDeformationCalculator $towerDeformationCalculator,
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

            if (! array_key_exists($key, $payload)) {
                continue;
            }

            $data = $payload[$key];
            $rows = $data['rows'] ?? [];
            $enabled = $type->isOptional() ? (bool)($data['enabled'] ?? false) : true;

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
                'rows' => $entity->getRows(),
            ];
        }

        $result = $calculation->getType() === CalculationTypeEnum::TOWER
            ? $this->addTowerDefaultData($calculation, $result)
            : $this->addDefaultData($calculation, $result);

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
                'rows' => $this->buildDefaultPillarForcesRows($calculation),
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
                'rows' => $this->buildDefaultStabilityRows($calculation, ElementTypeEnum::BELT),
            ];
        }

        if (empty($result[ResultTableTypeEnum::SUPERSTRUCTURE_STABILITY_BRACE->value])) {
            $result[ResultTableTypeEnum::SUPERSTRUCTURE_STABILITY_BRACE->value] = [
                'enabled' => false,
                'rows' => $this->buildDefaultStabilityRows($calculation, ElementTypeEnum::BRACE),
            ];
        }

        return $result;
    }

    /**
     * Значения по умолчанию для таблиц башни: несохранённые таблицы заполняются
     * строками по секциям/поясам башни и включаются согласно TOWER_ENABLED_BY_DEFAULT.
     */
    private function addTowerDefaultData(Calculation $calculation, array $result): array
    {
        $defaultRowsBuilders = [
            ResultTableTypeEnum::TOWER_BELT_STABILITY->value => fn(): array => $this->buildDefaultStabilityRows($calculation, ElementTypeEnum::BELT),
            ResultTableTypeEnum::TOWER_BRACE_STABILITY->value => fn(): array => $this->buildDefaultStabilityRows($calculation, ElementTypeEnum::BRACE),
            ResultTableTypeEnum::TOWER_SPACER_STABILITY->value => fn(): array => $this->buildDefaultStabilityRows($calculation, ElementTypeEnum::SPACER),
            ResultTableTypeEnum::TOWER_DEFORMATION->value => static fn(): array => [
                ['displacement' => null, 'angleY' => null, 'angleZ' => null],
            ],
            ResultTableTypeEnum::TOWER_ANCHOR_BOLTS->value => static fn(): array => [
                ['boltCount' => null, 'maxLoad' => null, 'diameter' => null, 'steel' => null, 'k0' => TowerAnchorBoltRowDto::DEFAULT_K0],
            ],
            ResultTableTypeEnum::TOWER_FLANGE_BOLTS->value => fn(): array => $this->buildDefaultFlangeBoltRows($calculation),
            ResultTableTypeEnum::TOWER_FLANGE_BOLTS_SHEAR->value => fn(): array => $this->buildDefaultFlangeBoltRows($calculation),
            ResultTableTypeEnum::TOWER_FOUNDATION_LOADS->value => fn(): array => $this->buildDefaultFoundationLoadRows($calculation),
            ResultTableTypeEnum::TOWER_LOAD_COMPARISON->value => static fn(): array => array_map(
                static fn(FoundationLoadKindEnum $kind): array => ['loadKind' => $kind->value],
                FoundationLoadKindEnum::cases(),
            ),
        ];

        foreach ($defaultRowsBuilders as $key => $buildRows) {
            if (! empty($result[$key])) {
                continue;
            }

            $result[$key] = [
                'enabled' => in_array(ResultTableTypeEnum::from($key), ResultTableTypeEnum::TOWER_ENABLED_BY_DEFAULT, true),
                'rows' => $buildRows(),
            ];
        }

        // Высота башни могла измениться после сохранения — пересчитываем допустимое перемещение и КИ
        $deformationKey = ResultTableTypeEnum::TOWER_DEFORMATION->value;
        $result[$deformationKey]['rows'] = $this->towerDeformationCalculator->calculateRows(
            $result[$deformationKey]['rows'],
            $calculation,
        );

        return $result;
    }

    /**
     * Строки таблицы нагрузок на фундаменты: каждое направление ветра × каждый пояс башни.
     *
     * @return array<int, array<string, mixed>>
     */
    private function buildDefaultFoundationLoadRows(Calculation $calculation): array
    {
        $beltsCount = $calculation->getPlatform()?->getFacetsCount()
            ?? $calculation->getCalculationData()?->getTowerSpecificData()?->facetsCount
            ?? 3;

        $rows = [];
        foreach (TowerWindDirectionEnum::cases() as $direction) {
            for ($beltNumber = 1; $beltNumber <= $beltsCount; $beltNumber++) {
                $rows[] = [
                    'direction' => $direction->value,
                    'beltNumber' => $beltNumber,
                    'rz' => null,
                    'rx' => null,
                    'ry' => null,
                ];
            }
        }

        return $rows;
    }

    /**
     * Строки таблицы фланцевых болтов: по одному стыку на верх каждой секции, кроме самой верхней.
     *
     * @return array<int, array<string, mixed>>
     */
    private function buildDefaultFlangeBoltRows(Calculation $calculation): array
    {
        $sections = array_values(array_filter(
            $calculation->getPlatform()?->getSortSectionsByNumber() ?? [],
            static fn(PlatformSection $section): bool => ! $section->isStrut(),
        ));
        array_pop($sections);

        $rows = [];
        foreach ($sections as $index => $section) {
            $rows[] = [
                'jointNumber' => $index + 1,
                'mark' => $section->getMountHeightTopM(),
                'boltCount' => null,
                'maxLoad' => null,
                'diameter' => null,
                'strengthClass' => null,
            ];
        }

        return $rows;
    }

    private function buildDefaultStabilityRows(Calculation $calculation, ElementTypeEnum $onlyType): array
    {
        $rows = [];

        foreach ($calculation->getPlatform()?->getSortSectionsByNumber() ?? [] as $section) {
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
    private function buildDefaultPillarForcesRows(Calculation $calculation): array
    {
        $specificData = $calculation->getCalculationData()->getConcretePillarSpecificData();
        $pillarEnum = $specificData->toEnumPillar();
        $pillarHeight = $specificData->pillarHeight;

        $mAllowable = $pillarEnum->getAllowableMomentByStrength();
        $lastMark = $pillarHeight !== null ? SimpleCalculator::lastMarkAboveGround($pillarHeight) : -1;

        if ($lastMark < 0) {
            return [
                ['mark' => 0, 'pillarType' => $pillarEnum->value, 'mAllowable' => $mAllowable],
            ];
        }

        $strengthening = $specificData->strengthening;

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

            if ($strengthening) {
                $strengtheningHeight = $strengthening->strengtheningHeight;
                $diffHeight = $strengtheningHeight - $mark;
                if ($diffHeight > 0 && $diffHeight < 1) {
                    $rows[] = [
                        'mark' => $strengtheningHeight,
                        'pillarType' => $pillarEnum->value,
                        'mCalc' => null,
                        'mAllowable' => $mAllowable,
                        'kMax' => null,
                        'mAllowableManual' => false,
                        'sectionDataAvailable' => true,
                    ];
                }
            }
        }

        return $rows;
    }
}
