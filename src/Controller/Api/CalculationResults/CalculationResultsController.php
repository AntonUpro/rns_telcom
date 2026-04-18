<?php

declare(strict_types=1);

namespace App\Controller\Api\CalculationResults;

use App\Controller\Api\AbstractApiController;
use App\Enum\Gauge\GaugeProfileTypeEnum;
use App\Enum\Pillar\ElementTypeEnum;
use App\Enum\Pillar\PillarEnum;
use App\Repository\CalculationRepository;
use App\Service\Calculation\CalculationResult\CalculationResultService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Throwable;

#[Route('/api/v1')]
#[IsGranted('ROLE_USER')]
class CalculationResultsController extends AbstractApiController
{
    /**
     * Стандартные типы железобетонных опор.
     * TODO: вынести в отдельный enum или справочник БД при необходимости.
     */
    private const array POLE_TYPES = [
        'СК 22.1-1',
        'СК 26.1-1.1',
        'СК 26.1-1.2',
        'СК 26.2-2',
        'СК 30.1-1',
        'СК 30.2-2',
        'СВ 95-2',
        'СВ 95-3',
        'СВ 110-2',
        'СВ 110-3.5',
        'СВ 110-5',
        'СВ 164-8',
        'СВ 164-10',
    ];

    /** Элементы для таблицы 3 (подкосы). */
    private const array TABLE3_ELEMENTS = [
        'подкос',
        'стойка',
    ];

    /** Элементы для таблицы 4 (пояса надстройки). */
    private const array TABLE4_ELEMENTS = [
        'пояс',
        'раскос',
        'ограждение',
        'рама',
        'стойка',
        'связь',
    ];

    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly CalculationRepository $calculationRepository,
        private readonly CalculationResultService $resultService,
    ) {
    }

    /**
     * GET /api/v1/calculation/calc-results/{calculationId}
     *
     * Возвращает справочные данные для таба «Результаты расчёта»:
     *   - profileTypes  — типы стальных профилей (GaugeProfileTypeEnum)
     *   - poleTypes     — типы ЖБ опор (для таблиц 1 и 2)
     *   - table3Elements / table4Elements — допустимые элементы в строках таблиц
     *   - savedData     — ранее сохранённые данные расчёта (пока null)
     *
     * TODO: сохранять/загружать введённые данные результатов расчёта из БД.
     */
    #[Route(
        '/calculation/calc-results/{calculationId}',
        name: 'api_calc_results_init',
        methods: ['GET'],
        requirements: ['calculationId' => '\d+'],
    )]
    public function getInitData(int $calculationId): JsonResponse
    {
        try {
            $calculation = $this->calculationRepository->findById($calculationId);
            if ($calculation === null) {
                return $this->errorResponse('Расчёт не найден', 404);
            }

            $savedData = $this->resultService->getAll($calculation);

            return $this->successResponse([
                'enums' => [
                    'profileTypes' => array_map(
                        static fn(GaugeProfileTypeEnum $case): array => [
                            'value' => $case->value,
                            'label' => $case->label(),
                        ],
                        GaugeProfileTypeEnum::cases(),
                    ),
                    'pillarTypes' => array_map(
                        static fn(PillarEnum $case): array => [
                            'value' => $case->value,
                            'allowableMoment' => $case->getAllowableMomentByStrength(),
                            'momentByCrackFormation' => $case->getMomentByCrackFormation(),
                        ],
                        PillarEnum::cases(),
                    ),
                    'elementTypes' => ElementTypeEnum::toOptions(),
                ],
                'savedData' => $savedData ?: null,
            ]);
        } catch (Throwable $e) {
            $this->logger->error(
                sprintf('Ошибка загрузки данных результатов расчёта %d: %s', $calculationId, $e->getMessage()),
                ['trace' => $e->getTraceAsString()],
            );

            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * POST /api/v1/calculation/calc-results/{calculationId}/calculate
     *
     * Принимает данные всех таблиц результатов и возвращает вычисленные значения.
     *
     * Ожидаемый формат тела запроса:
     * {
     *   "table1": { "rows": [{ "mark": float, "poleType": string, "mCalc": float }] },
     *   "table2": { "rows": [{ "mark": float, "poleType": string, "crackWidthAllowable": float }] },
     *   "table3": { "enabled": bool, "rows": [{ "element": string, "profileType": string,
     *               "sectionDesignation": string, "area": float, "momentResistance": float,
     *               "nCalc": float, "mCalc": float, "ry": float }] },
     *   "table4": { "enabled": bool, "rows": [...] },
     *   "table5": { "enabled": bool, "rows": [{ "mark": float, "profileType": string, ... }] },
     *   "table6": { "enabled": bool, "rows": [{ "loadType": string, "n": float, "q": float, "m": float }] },
     *   "table7": { "enabled": bool, "rows": [{ "mark": float, "angleAllowable": float }] },
     *   "table8": { "enabled": bool, "rows": [{ "q": float, "betaU": float }] }
     * }
     *
     * TODO: реализовать логику расчёта:
     *   - table1: Мдоп по типу опоры и отметке (нужна база данных несущей способности опор),
     *             k(max) = Mрасч / Мдоп
     *   - table2: расч. ширина трещин из нормативных нагрузок по СП 63.13330,
     *             k(max) = ширина_расч / ширина_доп
     *   - table3/4: σ = Nрасч*10/A + Mрасч*100/Wy (тс→кН, приведённые единицы),
     *               Кисп = σ / Ry
     *   - table5: аналогично table3/4
     */
    #[Route(
        '/calculation/calc-results/{calculationId}/calculate',
        name: 'api_calc_results_calculate',
        methods: ['POST'],
        requirements: ['calculationId' => '\d+'],
    )]
    public function calculate(int $calculationId, Request $request): JsonResponse
    {
        try {
            $calculation = $this->calculationRepository->findById($calculationId);
            if ($calculation === null) {
                return $this->errorResponse('Расчёт не найден', 404);
            }

            $payload = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);

            $this->resultService->saveAll($calculation, $payload);

            $this->logger->info(
                sprintf('Данные результатов расчёта %d сохранены', $calculationId),
            );

            // TODO: реализовать логику вычисления computed-полей и возвращать их фронтенду.
            // Пока возвращаем входные данные без изменений.
            return $this->successResponse([
                'message'              => 'Данные сохранены. Логика расчёта будет реализована.',
                'pillar_forces'        => $payload['pillar_forces']        ?? ['rows' => []],
                'crack_opening'        => $payload['crack_opening']        ?? ['rows' => []],
                'brace_stress'         => $payload['brace_stress']         ?? ['enabled' => false, 'rows' => []],
                'superstructure_stress'=> $payload['superstructure_stress']?? ['enabled' => false, 'rows' => []],
                'platform_forces'      => $payload['platform_forces']      ?? ['enabled' => false, 'rows' => []],
                // TODO: base_forces — нет computed-полей, только входные данные
                'base_forces'          => $payload['base_forces']          ?? ['enabled' => false, 'rows' => []],
                // TODO: deformation — displacement, angleMax, kUse из программного ПК
                'deformation'          => $payload['deformation']          ?? ['enabled' => false, 'rows' => []],
                // TODO: foundation — qU, beta, kUseStability, kUseDeformation из расчёта фундамента
                'foundation'           => $payload['foundation']           ?? ['enabled' => false, 'rows' => []],
            ]);
        } catch (Throwable $e) {
            $this->logger->error(
                sprintf('Ошибка расчёта результатов для расчёта %d: %s', $calculationId, $e->getMessage()),
                ['trace' => $e->getTraceAsString()],
            );

            return $this->errorResponse($e->getMessage());
        }
    }
}
