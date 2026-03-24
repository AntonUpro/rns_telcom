<?php

declare(strict_types=1);

namespace App\Controller\Api\TotalLoad;

use App\Controller\Api\AbstractApiController;
use App\Service\Calculation\TotalLoad\TotalLoadService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Throwable;

#[Route('/api/v1')]
#[IsGranted('ROLE_USER')]
class TotalLoadController extends AbstractApiController
{
    public function __construct(
        private readonly TotalLoadService $totalLoadService,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * Возвращает суммарную нагрузку для таба 5 по трём таблицам:
     *   - pillarSections   — нагрузка на ствол и коммуникации
     *   - platformSections — нагрузка на площадку и надстройку
     *   - equipmentHeights — нагрузка на оборудование по высотным отметкам
     */
    #[Route('/calculation/total-load/{calculationId}', name: 'api_total_load', methods: ['GET'], requirements: ['calculationId' => '\d+'])]
    public function getTotalLoad(int $calculationId): JsonResponse
    {
        try {
            $result = $this->totalLoadService->getTotalLoad($calculationId);

            return $this->successResponse($result->toArray());
        } catch (Throwable $e) {
            $this->logger->error(
                sprintf('Ошибка получения суммарной нагрузки для расчёта %d: %s', $calculationId, $e->getMessage()),
                ['trace' => $e->getTraceAsString()],
            );

            return $this->errorResponse($e->getMessage());
        }
    }
}
