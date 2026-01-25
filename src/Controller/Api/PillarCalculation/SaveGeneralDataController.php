<?php

declare(strict_types=1);

namespace App\Controller\Api\PillarCalculation;

use App\Controller\Api\AbstractApiController;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Throwable;

final class SaveGeneralDataController extends AbstractApiController
{
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {
    }

    public function saveGeneralData(Request $request): JsonResponse
    {
        try {


            return $this->successResponse();
        } catch (Throwable $e) {
            $this->logger->error(
                sprintf('Ошибка сохранения данных: %s', $e->getMessage()),
                ['trace' => $e->getTraceAsString()]
            );
            return $this->errorResponse($e->getMessage());
        }
    }
}
