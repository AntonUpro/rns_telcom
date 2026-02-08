<?php

declare(strict_types=1);

namespace App\Controller\Api\Common\Equipment;


use App\Controller\Api\AbstractApiController;
use App\Dto\Calculation\Equipment\Calculate\EquipmentCalculationResult;
use App\Dto\Equipment\AddEquipmentDto;
use App\Enum\Equipment\EquipmentTypeEnum;
use App\Service\Calculation\Equipment\CalculationWindEquipmentService;
use App\Service\Equipment\AddEquipmentService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Throwable;

#[Route('/api/v1')]
#[IsGranted('ROLE_USER')]
class CalculationAndGenerateFileEquipmentController extends AbstractApiController
{
    public function __construct(
        private CalculationWindEquipmentService $calculationWindEquipmentService,
        private LoggerInterface $logger,
    ) {
    }

    #[Route('/calculation/equipment/calculate', name: 'api_calculation_and_generate_file_equipment', methods: ['POST'])]
    public function calculateAndGenerateFileEquipment(Request $request): JsonResponse
    {
        try {
            $calculationId = $request->getPayload()->get('calculationId');

            $result = $this->calculationWindEquipmentService->calculate($calculationId);

            return $this->successResponse(array_map(fn(EquipmentCalculationResult $dto) => $dto->toArray(), $result));
        } catch (Throwable $throwable) {
            $this->logger->error(sprintf('Ошибка при выполнении расчета: %s', $throwable->getMessage()), [
                'trace' => $throwable->getTraceAsString(),
                'file' => $throwable->getFile(),
                'line' => $throwable->getLine(),
                'previous' => $throwable->getPrevious() ?? null,
            ]);

            return $this->errorResponse(
                sprintf('Ошибка при выполнении расчета: %s', $throwable->getMessage())
            );
        }
    }
}
