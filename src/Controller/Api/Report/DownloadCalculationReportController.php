<?php

declare(strict_types=1);

namespace App\Controller\Api\Report;

use App\Controller\Api\AbstractApiController;
use App\Exception\NotFoundException;
use App\Service\DocumentGenerator\CalculationReportGenerator;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Throwable;

#[Route('/api/v1')]
#[IsGranted('ROLE_USER')]
class DownloadCalculationReportController extends AbstractApiController
{
    public function __construct(
        private readonly CalculationReportGenerator $reportGenerator,
        private readonly LoggerInterface            $logger,
    ) {
    }

    /**
     * GET /api/v1/calculation/{calculationId}/report
     * Генерирует DOCX-отчёт и отдаёт его как вложение.
     */
    #[Route('/calculation/{calculationId}/report', name: 'api_download_calculation_report', methods: ['GET'])]
    public function download(int $calculationId): BinaryFileResponse|JsonResponse
    {
        $tmpDir = sys_get_temp_dir() . '/rns_reports';

        try {
            $filePath = $this->reportGenerator->generate($calculationId, $tmpDir);
        } catch (NotFoundException $e) {
            return $this->errorResponse($e->getMessage());
        } catch (Throwable $e) {
            $this->logger->error('Ошибка генерации отчёта расчёта', [
                'calculationId' => $calculationId,
                'error'         => $e->getMessage(),
                'trace'         => $e->getTraceAsString(),
            ]);

            return $this->errorResponse('Ошибка генерации файла: ' . $e->getMessage());
        }

        $response = new BinaryFileResponse($filePath);
        $response->headers->set(
            'Content-Type',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        );
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            sprintf('calculation_%d.docx', $calculationId),
        );
        $response->deleteFileAfterSend(true);

        return $response;
    }
}
