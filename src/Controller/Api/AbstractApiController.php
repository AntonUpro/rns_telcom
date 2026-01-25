<?php

declare(strict_types=1);

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractApiController extends AbstractController
{
    protected function successResponse(mixed $data = []): JsonResponse
    {
        return $this->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    protected function errorResponse(string $error, mixed $data = []): JsonResponse
    {
        return $this->json([
            'success' => false,
            'error' => $error,
            'data' => $data,
        ], Response::HTTP_BAD_REQUEST);
    }
}
