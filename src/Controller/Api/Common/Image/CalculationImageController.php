<?php

declare(strict_types=1);

namespace App\Controller\Api\Common\Image;

use App\Controller\Api\AbstractApiController;
use App\Entity\CalculationImage;
use App\Repository\CalculationImageRepository;
use App\Service\Calculation\Image\CalculationImageService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Throwable;

#[Route('/api/v1')]
#[IsGranted('ROLE_USER')]
class CalculationImageController extends AbstractApiController
{
    public function __construct(
        private readonly CalculationImageService    $imageService,
        private readonly CalculationImageRepository $imageRepository,
        private readonly LoggerInterface            $logger
    ) {
    }

    /**
     * GET /api/v1/calculation/{calculationId}/images
     * Возвращает список метаданных изображений для расчёта.
     */
    #[Route('/calculation/{calculationId}/images', name: 'api_get_calculation_images', methods: ['GET'])]
    public function list(int $calculationId): JsonResponse
    {
        try {
            $images = $this->imageService->getImages($calculationId);

            return $this->successResponse($this->imageService->imagesToArray($images));
        } catch (Throwable $e) {
            $this->logger->error('Ошибка получения изображений расчёта', [
                'calculationId' => $calculationId,
                'error'         => $e->getMessage(),
            ]);

            return $this->errorResponse('Ошибка получения изображений');
        }
    }

    /**
     * POST /api/v1/calculation/{calculationId}/images
     * Загружает одно изображение для расчёта.
     *
     * Form fields:
     *   - imageType  (string) — тип изображения
     *   - file       (file)   — загружаемый файл
     */
    #[Route('/calculation/{calculationId}/images', name: 'api_upload_calculation_image', methods: ['POST'])]
    public function upload(int $calculationId, Request $request): JsonResponse
    {
        try {
            $imageType = (string)$request->request->get('imageType', '');
            if ($imageType === '') {
                return $this->errorResponse('Не указан тип изображения (imageType)');
            }

            if (!in_array($imageType, CalculationImage::ALLOWED_TYPES, true)) {
                return $this->errorResponse(sprintf('Недопустимый тип изображения: %s', $imageType));
            }

            $file = $request->files->get('file');
            if ($file === null) {
                return $this->errorResponse('Файл не передан');
            }

            $image = $this->imageService->saveImage($calculationId, $imageType, $file);

            return $this->successResponse($image->toArray());
        } catch (Throwable $e) {
            $this->logger->error('Ошибка загрузки изображения расчёта', [
                'calculationId' => $calculationId,
                'error'         => $e->getMessage(),
                'trace'         => $e->getTraceAsString(),
            ]);

            return $this->errorResponse('Ошибка загрузки изображения: ' . $e->getMessage());
        }
    }

    /**
     * GET /api/v1/calculation/image/{imageId}/file
     * Отдаёт бинарный файл изображения.
     */
    #[Route('/calculation/image/{imageId}/file', name: 'api_get_calculation_image_file', methods: ['GET'])]
    public function getFile(int $imageId): BinaryFileResponse|JsonResponse
    {
        try {
            $image = $this->imageRepository->find($imageId);
            if ($image === null) {
                return $this->errorResponse('Изображение не найдено');
            }

            $absolutePath = $this->imageService->getAbsoluteFilePath($image);
            if (!is_file($absolutePath)) {
                return $this->errorResponse('Файл изображения не найден на диске');
            }

            $response = new BinaryFileResponse($absolutePath);
            $response->headers->set('Content-Type', $image->getMimeType());
            $response->setContentDisposition(
                ResponseHeaderBag::DISPOSITION_INLINE,
                $image->getOriginalFileName()
            );

            return $response;
        } catch (Throwable $e) {
            $this->logger->error('Ошибка отдачи файла изображения', [
                'imageId' => $imageId,
                'error'   => $e->getMessage(),
            ]);

            return $this->errorResponse('Ошибка получения файла');
        }
    }
}
