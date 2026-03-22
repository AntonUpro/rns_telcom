<?php

namespace App\Controller\Api\PillarCalculation;

use App\Controller\Api\AbstractApiController;
use App\Dto\Calculation\Pillar\Platform\PlatformSaveDataDto;
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
        private readonly LoggerInterface $logger,
    ) {
    }

    #[Route('/save/calculation/platform', name: 'save_data_platform', methods: ['POST'])]
    public function createSection(Request $request): Response
    {
        try {
            $data = json_decode($request->getContent(), true);

            $platformSaveDataDto = PlatformSaveDataDto::fromArray($data);

            $this->savePlatformDataService->savePlatformData($platformSaveDataDto);

            return $this->successResponse();
        } catch (Throwable $e) {
            $this->logger->error(
                sprintf('Ошибка сохранения данных площадки: %s', $e->getMessage()),
                ['trace' => $e->getTraceAsString()]
            );
            return $this->errorResponse($e->getMessage());
        }
    }
}
