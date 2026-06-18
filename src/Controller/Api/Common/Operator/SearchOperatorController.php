<?php

declare(strict_types=1);

namespace App\Controller\Api\Common\Operator;

use App\Controller\Api\AbstractApiController;
use App\Service\Operator\OperatorSearchService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Throwable;

#[Route('/api/v1')]
#[IsGranted('ROLE_ENGINEER')]
class SearchOperatorController extends AbstractApiController
{
    public function __construct(
        private readonly OperatorSearchService $operatorSearchService,
        private readonly LoggerInterface $logger,
    ) {
    }

    #[Route('/operator/search', name: 'api_operator_search', methods: ['GET'])]
    public function search(Request $request): JsonResponse
    {
        try {
            $query = (string) $request->query->get('query', '');

            if (mb_strlen($query) < 2) {
                return $this->errorResponse('В запросе должно быть не менее 2 символов');
            }

            return $this->successResponse($this->operatorSearchService->search($query));
        } catch (Throwable $throwable) {
            $this->logger->error(sprintf('Ошибка поиска оператора: %s', $throwable->getMessage()), [
                'trace' => $throwable->getTraceAsString(),
                'file'  => $throwable->getFile(),
                'line'  => $throwable->getLine(),
            ]);

            return $this->errorResponse('Ошибка поиска оператора');
        }
    }
}
