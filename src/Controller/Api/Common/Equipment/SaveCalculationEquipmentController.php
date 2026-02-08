<?php

declare(strict_types=1);

namespace App\Controller\Api\Common\Equipment;

use App\Controller\Api\AbstractApiController;
use App\Dto\Calculation\Equipment\AllEquipmentDto;
use App\Service\Calculation\Equipment\GetCalculationEquipmentService;
use App\Service\Calculation\Equipment\SaveCalculationEquipmentService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Throwable;

#[Route('/api/v1')]
#[IsGranted('ROLE_USER')]
class SaveCalculationEquipmentController extends AbstractApiController
{
    public function __construct(
        private readonly SaveCalculationEquipmentService $saveCalculationEquipmentService,
        private readonly GetCalculationEquipmentService $getCalculationEquipmentService,
        private readonly LoggerInterface $logger
    ) {
    }

    #[Route('/save/calculation/equipment', name: 'api_save_calculation_equipment', methods: ['POST'])]
    public function searchEquipment(Request $request): JsonResponse
    {
        try {
            $calculationId = (int)$request->getPayload()->get('calculationId');
            if (! $calculationId) {
                return $this->errorResponse('Не указан идентификатор расчета');
            }
            $equipmentSave = $request->getPayload()->all('equipment');
            $equipment = AllEquipmentDto::create($equipmentSave);

            $this->saveCalculationEquipmentService->saveEquipmentForCalculation($equipment, $calculationId);

            return $this->successResponse($this->getCalculationEquipmentService->getEquipmentByCalculationId($calculationId));
        } catch (Throwable $throwable) {
            $this->logger->error(sprintf('Ошибка сохранения оборудования: %s', $throwable->getMessage()), [
                'trace' => $throwable->getTraceAsString(),
                'file' => $throwable->getFile(),
                'line' => $throwable->getLine(),
                'previous' => $throwable->getPrevious() ?? null,
            ]);

            return $this->errorResponse('Ошибка сохранения оборудования: %s', $throwable->getMessage());
        }
    }
}
