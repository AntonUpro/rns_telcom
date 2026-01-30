<?php

declare(strict_types=1);

namespace App\Controller\Api\PillarCalculation;

use App\Controller\Api\AbstractApiController;
use App\Dto\Calculation\PillarCalculationDataDto;
use App\Exception\NotFoundCalculationDataException;
use App\Service\Calculation\PillarCalculationService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Throwable;

#[Route('/api/v1')]
#[IsGranted('ROLE_USER')]
final class SaveGeneralDataController extends AbstractApiController
{
    public function __construct(
        private readonly PillarCalculationService $pillarCalculationService,
        private readonly LoggerInterface $logger,
    ) {
    }

    #[Route('/save/general-data', name: 'api_save_general_data', methods: ['POST'])]

    public function saveGeneralData(Request $request): JsonResponse
    {
        try {
            $params = $request->getPayload()->all();
            $pillarCalculationDataDto = PillarCalculationDataDto::fromRequest($params);

            $this->pillarCalculationService->saveGeneralData($pillarCalculationDataDto);
            return $this->successResponse($pillarCalculationDataDto->toArray());
        } catch (Throwable $e) {
            $this->logger->error(
                sprintf('Ошибка сохранения данных: %s', $e->getMessage()),
                ['trace' => $e->getTraceAsString()]
            );
            return $this->errorResponse($e->getMessage());
        }
    }

    #[Route('/calculation/{id}', name: 'api_get-calculation', methods: ['GET'])]
    public function getCalculationData(Request $request): JsonResponse
    {
        try {
            $calculationId = (int) $request->attributes->get('id');
            if (!$calculationId) {
                return $this->errorResponse('Не указан идентификатор расчета');
            }

            $calculationDto = $this->pillarCalculationService->getCalculationInfo($calculationId);

            return $this->successResponse($calculationDto->toArray());
        } catch (NotFoundCalculationDataException $e) {
            return $this->successResponse(['calculationId' => $calculationId]);

        } catch (Throwable $e) {
            $this->logger->error(
                sprintf('Ошибка сохранения данных: %s', $e->getMessage()),
                ['trace' => $e->getTraceAsString()]
            );
            return $this->errorResponse($e->getMessage());
        }
    }
}
