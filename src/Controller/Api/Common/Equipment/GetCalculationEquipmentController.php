<?php

declare(strict_types=1);

namespace App\Controller\Api\Common\Equipment;

use App\Controller\Api\AbstractApiController;
use App\Service\Calculation\Equipment\GetCalculationEquipmentService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Throwable;

#[Route('/api/v1')]
#[IsGranted('ROLE_USER')]
final class GetCalculationEquipmentController extends AbstractApiController
{
    public function __construct(
        private GetCalculationEquipmentService $getCalculationEquipmentService,
        private LoggerInterface $logger,
    ) {
    }

    #[Route('/calculation/equipment', name: 'api_get_calculation_equipment', methods: ['GET'])]
    public function searchEquipment(Request $request): JsonResponse
    {
        try {
            $calculationId = (int) $request->query->get('calculationId');;
            if (! $calculationId) {
                return $this->errorResponse('Не указан идентификатор расчета');
            }

            $allEquipment = $this->getCalculationEquipmentService->getEquipmentByCalculationId($calculationId);

            return $this->successResponse($allEquipment->toArray());
        } catch (Throwable $throwable) {
            $this->logger->error(sprintf('Ошибка получения оборудования: %s', $throwable->getMessage()), [
                'trace' => $throwable->getTraceAsString(),
                'file' => $throwable->getFile(),
                'line' => $throwable->getLine(),
                'previous' => $throwable->getPrevious() ?? null,
            ]);

            return $this->errorResponse('Ошибка получения оборудования: %s', $throwable->getMessage());
        }
    }
}
