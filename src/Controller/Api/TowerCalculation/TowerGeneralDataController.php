<?php

declare(strict_types=1);

namespace App\Controller\Api\TowerCalculation;

use App\Controller\Api\AbstractApiController;
use App\Dto\Calculation\TowerCalculationDataDto;
use App\Exception\NotFoundCalculationDataException;
use App\Service\Calculation\TowerCalculationService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Throwable;

#[Route('/api/v1')]
#[IsGranted('ROLE_ENGINEER')]
final class TowerGeneralDataController extends AbstractApiController
{
    public function __construct(
        private readonly TowerCalculationService $towerCalculationService,
        private readonly LoggerInterface $logger,
    ) {
    }

    #[Route('/save/tower-general-data', name: 'api_save_tower_general_data', methods: ['POST'])]
    public function saveGeneralData(Request $request): JsonResponse
    {
        try {
            $params = $request->getPayload()->all();
            $towerCalculationDataDto = TowerCalculationDataDto::fromRequest($params);

            $this->towerCalculationService->saveGeneralData($towerCalculationDataDto);
            return $this->successResponse($towerCalculationDataDto->toArray());
        } catch (Throwable $e) {
            $this->logger->error(
                sprintf('Ошибка сохранения данных башни: %s', $e->getMessage()),
                ['trace' => $e->getTraceAsString()]
            );
            return $this->errorResponse($e->getMessage());
        }
    }

    #[Route('/calculation/tower/{id}', name: 'api_get_tower_calculation', methods: ['GET'])]
    public function getCalculationData(Request $request): JsonResponse
    {
        try {
            $calculationId = (int) $request->attributes->get('id');
            if (!$calculationId) {
                return $this->errorResponse('Не указан идентификатор расчета');
            }

            $calculationDto = $this->towerCalculationService->getCalculationInfo($calculationId);

            return $this->successResponse($calculationDto->toArray());
        } catch (NotFoundCalculationDataException $e) {
            return $this->successResponse(['calculationId' => $calculationId]);
        } catch (Throwable $e) {
            $this->logger->error(
                sprintf('Ошибка получения данных башни: %s', $e->getMessage()),
                ['trace' => $e->getTraceAsString()]
            );
            return $this->errorResponse($e->getMessage());
        }
    }
}
