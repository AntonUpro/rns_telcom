<?php

declare(strict_types=1);

namespace App\Service\Calculation\Image;

use App\Entity\CalculationImage;
use App\Repository\CalculationImageRepository;
use App\Repository\CalculationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use InvalidArgumentException;
use RuntimeException;

class CalculationImageService
{
    private string $uploadsBaseDir;

    public function __construct(
        private readonly CalculationImageRepository $imageRepository,
        private readonly CalculationRepository $calculationRepository,
        private readonly EntityManagerInterface $entityManager,
        string $projectDir
    ) {
        $this->uploadsBaseDir = $projectDir . '/var/uploads/calculation_images';
    }

    public function saveImage(int $calculationId, string $imageType, UploadedFile $file): CalculationImage
    {
        if (!in_array($imageType, CalculationImage::ALLOWED_TYPES, true)) {
            throw new InvalidArgumentException(sprintf('Недопустимый тип изображения: %s', $imageType));
        }

        $calculation = $this->calculationRepository->find($calculationId);
        if ($calculation === null) {
            throw new InvalidArgumentException(sprintf('Расчет с id=%d не найден', $calculationId));
        }

        $calcDir = $this->uploadsBaseDir . '/' . $calculationId;
        if (!is_dir($calcDir) && !mkdir($calcDir, 0755, true) && !is_dir($calcDir)) {
            throw new RuntimeException(sprintf('Не удалось создать директорию: %s', $calcDir));
        }

        $extension    = strtolower($file->getClientOriginalExtension() ?: 'png');
        $storedName   = $imageType . '_' . uniqid('', true) . '.' . $extension;
        $relativePath = $calculationId . '/' . $storedName;

        // Получаем метаданные ДО move(), так как после него временный файл удаляется
        $originalName = $file->getClientOriginalName() ?: $storedName;
        $mimeType     = $file->getMimeType() ?? 'image/png';
        $fileSize     = $file->getSize();

        $file->move($calcDir, $storedName);

        $existingFile = $this->imageRepository->findByCalculationAndType($calculationId, $imageType);

        if ($existingFile !== null) {
            $oldFile = $this->uploadsBaseDir . '/' . $existingFile->getFilePath();
            if (is_file($oldFile)) {
                unlink($oldFile);
            }

            $existingFile->setOriginalFileName($originalName);
            $existingFile->setStoredFileName($storedName);
            $existingFile->setFilePath($relativePath);
            $existingFile->setMimeType($mimeType);
            $existingFile->setFileSize($fileSize);
            $existingFile->setVersion($existingFile->getVersion() + 1);

            $this->entityManager->flush();

            return $existingFile;
        }

        $image = new CalculationImage();
        $image->setCalculation($calculation);
        $image->setImageType($imageType);
        $image->setOriginalFileName($originalName);
        $image->setStoredFileName($storedName);
        $image->setFilePath($relativePath);
        $image->setMimeType($mimeType);
        $image->setFileSize($fileSize);

        $this->entityManager->persist($image);
        $this->entityManager->flush();

        return $image;
    }

    /**
     * @return CalculationImage[]
     */
    public function getImages(int $calculationId): array
    {
        return $this->imageRepository->findByCalculation($calculationId);
    }

    public function getAbsoluteFilePath(CalculationImage $image): string
    {
        return $this->uploadsBaseDir . '/' . $image->getFilePath();
    }

    /**
     * @param CalculationImage[] $images
     */
    public function imagesToArray(array $images): array
    {
        return array_map(fn(CalculationImage $img) => $img->toArray(), $images);
    }
}
