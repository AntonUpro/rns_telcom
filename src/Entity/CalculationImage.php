<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\CalculationImageRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use DateTimeImmutable;

#[ORM\Entity(repositoryClass: CalculationImageRepository::class)]
#[ORM\Table(name: 'calculation_images')]
#[ORM\Index(name: 'idx_calculation_images_calc_id', columns: ['calculation_id'])]
#[ORM\Index(name: 'idx_calculation_images_calc_type', columns: ['calculation_id', 'image_type'])]
#[ORM\HasLifecycleCallbacks]
class CalculationImage
{
    public const TYPE_SCHEME = 'scheme';
    public const TYPE_SCHEME_PC = 'scheme_pc';
    public const TYPE_SECTIONS = 'sections';
    public const TYPE_MOSAIC_N = 'mosaic_n';
    public const TYPE_MOSAIC_M = 'mosaic_m';
    public const TYPE_MOSAIC_DISPLACEMENT = 'mosaic_displacement';

    public const ALLOWED_TYPES = [
        self::TYPE_SCHEME,
        self::TYPE_SCHEME_PC,
        self::TYPE_SECTIONS,
        self::TYPE_MOSAIC_N,
        self::TYPE_MOSAIC_M,
        self::TYPE_MOSAIC_DISPLACEMENT,
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Calculation::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Calculation $calculation = null;

    #[ORM\Column(length: 50)]
    private string $imageType;

    #[ORM\Column(length: 255)]
    private string $originalFileName;

    #[ORM\Column(length: 255)]
    private string $storedFileName;

    /** Относительный путь от базовой директории загрузок: {calculationId}/{storedFileName} */
    #[ORM\Column(length: 500)]
    private string $filePath;

    #[ORM\Column(length: 100)]
    private string $mimeType;

    #[ORM\Column(type: Types::INTEGER)]
    private int $fileSize;

    #[ORM\Column(type: Types::INTEGER, options: ['default' => 1])]
    private int $version = 1;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCalculation(): ?Calculation
    {
        return $this->calculation;
    }

    public function setCalculation(?Calculation $calculation): static
    {
        $this->calculation = $calculation;
        return $this;
    }

    public function getImageType(): string
    {
        return $this->imageType;
    }

    public function setImageType(string $imageType): static
    {
        $this->imageType = $imageType;
        return $this;
    }

    public function getOriginalFileName(): string
    {
        return $this->originalFileName;
    }

    public function setOriginalFileName(string $originalFileName): static
    {
        $this->originalFileName = $originalFileName;
        return $this;
    }

    public function getStoredFileName(): string
    {
        return $this->storedFileName;
    }

    public function setStoredFileName(string $storedFileName): static
    {
        $this->storedFileName = $storedFileName;
        return $this;
    }

    public function getFilePath(): string
    {
        return $this->filePath;
    }

    public function setFilePath(string $filePath): static
    {
        $this->filePath = $filePath;
        return $this;
    }

    public function getMimeType(): string
    {
        return $this->mimeType;
    }

    public function setMimeType(string $mimeType): static
    {
        $this->mimeType = $mimeType;
        return $this;
    }

    public function getFileSize(): int
    {
        return $this->fileSize;
    }

    public function setFileSize(int $fileSize): static
    {
        $this->fileSize = $fileSize;
        return $this;
    }

    public function getVersion(): int
    {
        return $this->version;
    }

    public function setVersion(int $version): static
    {
        $this->version = $version;
        return $this;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'imageType' => $this->imageType,
            'originalFileName' => $this->originalFileName,
            'mimeType' => $this->mimeType,
            'fileSize' => $this->fileSize,
            'version' => $this->version,
            'createdAt' => $this->createdAt->format('Y-m-d H:i:s'),
            'updatedAt' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
