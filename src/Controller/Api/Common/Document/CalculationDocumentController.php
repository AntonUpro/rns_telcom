<?php

declare(strict_types=1);

namespace App\Controller\Api\Common\Document;

use App\Controller\Api\AbstractApiController;
use App\Service\Calculation\Document\CalculationDocumentService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Throwable;

#[Route('/api/v1')]
#[IsGranted('ROLE_USER')]
class CalculationDocumentController extends AbstractApiController
{
    public function __construct(
        private readonly CalculationDocumentService $documentService,
        private readonly LoggerInterface            $logger,
    ) {
    }

    /**
     * GET /api/v1/calculation/{calculationId}/documents
     * Возвращает список документов расчёта.
     */
    #[Route('/calculation/{calculationId}/documents', name: 'api_get_calculation_documents', methods: ['GET'])]
    public function list(int $calculationId): JsonResponse
    {
        try {
            $documents = $this->documentService->getDocuments($calculationId);

            return $this->successResponse($this->documentService->documentsToArray($documents));
        } catch (Throwable $e) {
            $this->logger->error('Ошибка получения документов расчёта', [
                'calculationId' => $calculationId,
                'error'         => $e->getMessage(),
            ]);

            return $this->errorResponse('Ошибка получения документов');
        }
    }

    /**
     * POST /api/v1/calculation/{calculationId}/documents
     * Сохраняет (полностью заменяет) список документов расчёта.
     *
     * Body (JSON): { "names": ["Документ 1", "Документ 2", ...] }
     */
    #[Route('/calculation/{calculationId}/documents', name: 'api_save_calculation_documents', methods: ['POST'])]
    public function save(int $calculationId, Request $request): JsonResponse
    {
        try {
            $body = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
        } catch (Throwable) {
            return $this->errorResponse('Некорректный JSON в теле запроса');
        }

        $names = $body['names'] ?? null;

        if (!is_array($names)) {
            return $this->errorResponse('Поле "names" должно быть массивом строк');
        }

        foreach ($names as $name) {
            if (!is_string($name)) {
                return $this->errorResponse('Каждый элемент "names" должен быть строкой');
            }

            if (mb_strlen(trim($name)) > 500) {
                return $this->errorResponse('Название документа не должно превышать 500 символов');
            }
        }

        try {
            $documents = $this->documentService->saveDocuments($calculationId, $names);

            return $this->successResponse($this->documentService->documentsToArray($documents));
        } catch (\InvalidArgumentException $e) {
            return $this->errorResponse($e->getMessage());
        } catch (Throwable $e) {
            $this->logger->error('Ошибка сохранения документов расчёта', [
                'calculationId' => $calculationId,
                'error'         => $e->getMessage(),
                'trace'         => $e->getTraceAsString(),
            ]);

            return $this->errorResponse('Ошибка сохранения документов');
        }
    }
}
