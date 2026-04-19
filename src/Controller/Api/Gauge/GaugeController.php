<?php

declare(strict_types=1);

namespace App\Controller\Api\Gauge;

use App\Controller\Api\AbstractApiController;
use App\Enum\Gauge\GaugeProfileTypeEnum;
use App\Service\Gauge\GaugeSearchService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/v1/gauge')]
class GaugeController extends AbstractApiController
{
    public function __construct(
        private readonly GaugeSearchService $searchService,
    ) {}

    /**
     * GET /api/v1/gauge/search?type=CHANNEL&q=10&limit=15
     *
     * Возвращает список профилей подходящих под запрос.
     * Параметры:
     *   type  — значение GaugeProfileTypeEnum (обязательно)
     *   q     — строка поиска (обязательно, мин. 1 символ)
     *   limit — макс. количество результатов (опц., 1–50, по умолчанию 15)
     */
    #[Route('/search', name: 'api_gauge_search', methods: ['GET'])]
    public function search(Request $request): JsonResponse
    {
        $typeValue = trim((string) $request->query->get('type', ''));
        $query     = trim((string) $request->query->get('q', ''));

        if ($typeValue === '' || $query === '') {
            return $this->errorResponse('Параметры type и q обязательны');
        }

        $type = GaugeProfileTypeEnum::tryFrom($typeValue);
        if ($type === null) {
            return $this->errorResponse(sprintf('Неизвестный тип сечения: %s', $typeValue));
        }

        $limit   = min(50, max(1, (int) $request->query->get('limit', 15)));
        $results = $this->searchService->search($type, $query, $limit);

        return $this->successResponse($results);
    }
}
