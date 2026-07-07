<?php

declare(strict_types=1);

namespace App\Controller\Api\StaticImage;

use App\Controller\Api\AbstractApiController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Отдаёт статичные картинки-подсказки (справочные схемы/таблицы из СП),
 * используемые бэкендом также для генерации Word-отчётов (папка static_image/
 * не находится в public/ и не обслуживается веб-сервером напрямую).
 */
#[Route('/api/v1/static-image')]
#[IsGranted('ROLE_ENGINEER')]
class StaticImageController extends AbstractApiController
{
    private const array ALLOWED_FILES = [
        'shame_brace.png',
        'table_32_SP.png',
    ];

    public function __construct(
        private readonly string $projectDir,
    ) {
    }

    #[Route(
        '/{filename}',
        name: 'api_static_image',
        methods: ['GET'],
    )]
    public function get(string $filename): Response
    {
        if (!in_array($filename, self::ALLOWED_FILES, true)) {
            throw $this->createNotFoundException();
        }

        $path = $this->projectDir . '/static_image/' . $filename;

        if (!is_file($path)) {
            throw $this->createNotFoundException();
        }

        return new BinaryFileResponse($path);
    }
}
