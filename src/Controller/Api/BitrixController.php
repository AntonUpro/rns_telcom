<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Service\BitrixService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Throwable;
use Exception;

#[Route('/api/v1')]
#[IsGranted('ROLE_USER')]
final class BitrixController extends AbstractApiController
{
    public function __construct(
        private BitrixService $bitrixService
    ) {
    }

    #[Route('/bitrix/load-object-data', name: 'api_bitrix_load_object_data', methods: ['POST'])]
    public function uploadDataFromBitrix(Request $request): JsonResponse
    {
        try {
            $objectCode = $request->getPayload()->get('objectCode');
            if (!$objectCode) {
                throw new Exception('Не передан обязательный параметр objectCode');
            }

            $bitrixData = $this->bitrixService->loadDataFromBitrix($objectCode);

            return $this->json([
                'success' => true,
                'data' => $bitrixData,
            ]);
        } catch (Throwable $exception) {
            return $this->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ]);
        }
    }
}
