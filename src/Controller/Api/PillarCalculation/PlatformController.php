<?php

namespace App\Controller\Api\PillarCalculation;

use App\Controller\Api\AbstractApiController;
use App\Dto\Calculation\Pillar\Platform\PlatformSaveDataDto;
use App\Service\Calculation\Pillar\Platform\GetPlatformDataService;
use App\Service\Calculation\Pillar\Platform\SavePlatformDataService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Throwable;

#[Route('/api/v1')]
#[IsGranted('ROLE_USER')]
class PlatformController extends AbstractApiController
{
    public function __construct(
        private readonly SavePlatformDataService $savePlatformDataService,
        private readonly GetPlatformDataService $getPlatformDataService,
        private readonly LoggerInterface $logger,
    ) {
    }

    #[Route('/calculation/platform/save', name: 'save_data_platform', methods: ['POST'])]
    public function createSection(Request $request): Response
    {
        try {
            $data = json_decode($request->getContent(), true);

            $platformSaveDataDto = PlatformSaveDataDto::fromArray($data);

            $this->savePlatformDataService->savePlatformData($platformSaveDataDto);

            $platformSaveDataDto = $this->getPlatformDataService->getPlatformData($platformSaveDataDto->calculationId);
            return $this->successResponse($platformSaveDataDto->toArray());
        } catch (Throwable $e) {
            $this->logger->error(
                sprintf('Ошибка сохранения данных площадки: %s', $e->getMessage()),
                ['trace' => $e->getTraceAsString()]
            );
            return $this->errorResponse($e->getMessage());
        }
    }

    #[Route('/calculation/platform/{calculationId}', name: 'get_platform_data', methods: ['GET'])]
    public function getPlatformData(int $calculationId): Response
    {
        try {
            $data = $this->getPlatformDataService->getPlatformData($calculationId);

            return $this->successResponse($data->toArray());
        } catch (Throwable $e) {
            $this->logger->error(
                sprintf('Ошибка получения данных площадки: %s', $e->getMessage()),
                ['trace' => $e->getTraceAsString()]
            );
            return $this->errorResponse($e->getMessage());
        }
    }
}
