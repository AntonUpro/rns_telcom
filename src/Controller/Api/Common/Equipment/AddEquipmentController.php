<?php

declare(strict_types=1);

namespace App\Controller\Api\Common\Equipment;

use App\Controller\Api\AbstractApiController;
use App\Dto\Equipment\AddEquipmentDto;
use App\Enum\Equipment\EquipmentTypeEnum;
use App\Service\Equipment\AddEquipmentService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Throwable;

#[Route('/api/v1')]
#[IsGranted('ROLE_USER')]
class AddEquipmentController extends AbstractApiController
{
    public function __construct(
        private AddEquipmentService $addEquipmentService,
        private LoggerInterface $logger,
    ) {
    }

    #[Route('/equipment/add', name: 'api_equipment_add', methods: ['POST'])]
    public function addEquipment(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $this->logger->info(sprintf('Добавление оборудования: %s', json_encode($data)));

            $equipmentType = EquipmentTypeEnum::from($data['type']);

            $this->addEquipmentService->addEquipment(new AddEquipmentDto(
                brand: trim($data['brand']),
                model: trim($data['model']),
                equipmentType: $equipmentType,
                hasDiameter: $equipmentType->isRrl(),
                diameter: $data['diameter'] ?? null,
                height: $data['height'] ?? null,
                width: $data['width'] ?? null,
                depth: $data['depth'] ?? null,
                weight: $data['weight'],
            ));

            return $this->successResponse();
        } catch (Throwable $throwable) {
            $this->logger->error(sprintf('Ошибка добавления оборудования: %s', $throwable->getMessage()), [
                'trace' => $throwable->getTraceAsString(),
                'file' => $throwable->getFile(),
                'line' => $throwable->getLine(),
                'previous' => $throwable->getPrevious() ?? null,
            ]);

            return $this->errorResponse(
                sprintf('Ошибка добавления оборудования: %s', $throwable->getMessage())
            );
        }
    }
}
