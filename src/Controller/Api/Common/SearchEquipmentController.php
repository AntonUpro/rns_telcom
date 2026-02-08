<?php

declare(strict_types=1);

namespace App\Controller\Api\Common;

use App\Controller\Api\AbstractApiController;
use App\Enum\Equipment\EquipmentTypeEnum;
use App\Service\Equipment\SearchEquipmentService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Throwable;

#[Route('/api/v1')]
#[IsGranted('ROLE_USER')]
class SearchEquipmentController extends AbstractApiController
{
    public function __construct(
        private SearchEquipmentService $searchEquipmentService,
        private LoggerInterface $logger,
    ) {
    }

    #[Route('/equipment/search', name: 'api_equipment_search', methods: ['GET'])]
    public function searchEquipment(Request $request): JsonResponse
    {
        try {
            $type = $request->query->get('type');
            $query = $request->query->get('query');

            if (empty($type)) {
                return $this->errorResponse('Не передан тип искомого оборудования');
            }
            if (empty($query) || mb_strlen($query) < 2) {
                return $this->errorResponse('В запросе должно быть не менее 2 символов');
            }

            $equipment = $this->searchEquipmentService->search($query, EquipmentTypeEnum::from($type));

            return $this->successResponse(
                $equipment
            );
        } catch (Throwable $throwable) {
            $this->logger->error(sprintf('Ошибка поиска оборудования: %s', $throwable->getMessage()), [
                'trace' => $throwable->getTraceAsString(),
                'file' => $throwable->getFile(),
                'line' => $throwable->getLine(),
                'previous' => $throwable->getPrevious() ?? null,
            ]);

            return $this->errorResponse('Ошибка поиска оборудования: %s', $throwable->getMessage());
        }
    }
}
