<?php

declare(strict_types=1);

namespace App\Controller\Api\CalculationResults;

use App\Controller\Api\AbstractApiController;
use App\Enum\Gauge\GaugeProfileTypeEnum;
use App\Enum\Pillar\PillarEnum;
use App\Repository\CalculationRepository;
use App\Service\Calculation\CalculationResult\CalculationResultCalculatorService;
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
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly CalculationRepository $calculationRepository,
        private readonly CalculationResultService $resultService,
        private readonly CalculationResultCalculatorService $calculatorService,
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

            $computed = $this->calculatorService->calculateAll($payload);

            $this->resultService->saveAll($calculation, $computed);

            $this->logger->info(
                sprintf('Результаты расчёта %d вычислены и сохранены', $calculationId),
            );

            return $this->successResponse($computed);
        } catch (Throwable $e) {
            $this->logger->error(
                sprintf('Ошибка расчёта результатов для расчёта %d: %s', $calculationId, $e->getMessage()),
                ['trace' => $e->getTraceAsString()],
            );

            return $this->errorResponse($e->getMessage());
        }
    }
}
