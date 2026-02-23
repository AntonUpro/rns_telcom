<?php

declare(strict_types=1);

namespace App\Controller\Api\PillarCalculation;

use App\Controller\Api\AbstractApiController;
use App\Service\Calculation\Pillar\PillarWindLoadCalculationService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Throwable;


#[Route('/api/v1')]
#[IsGranted('ROLE_USER')]
class WindLoadCalculationController extends AbstractApiController
{
    public function __construct(
        private readonly PillarWindLoadCalculationService $pillarWindLoadCalculationService,
        private readonly LoggerInterface $logger,
    ) {
    }

    #[Route('/calculation/wind-load', name: 'api_wind_load_calculation', methods: ['POST'])]
    public function saveGeneralData(Request $request): JsonResponse
    {
        try {
            $calculationId = (int) $request->getPayload()->get('calculationId');

            $sections = $this->pillarWindLoadCalculationService->calculate($calculationId);

            return $this->successResponse($sections->toArray());
        } catch (Throwable $e) {
            $this->logger->error(
                sprintf('Ошибка расчета общих данных столба: %s', $e->getMessage()),
                ['trace' => $e->getTraceAsString()]
            );
            return $this->errorResponse($e->getMessage());
        }
    }
}
