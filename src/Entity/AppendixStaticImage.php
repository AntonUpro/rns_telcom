<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\AppendixTypeEnum;
use App\Repository\AppendixStaticImageRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AppendixStaticImageRepository::class)]
#[ORM\Table(name: 'appendix_static_images')]
#[ORM\Index(name: 'idx_appendix_static_images_type_pos', columns: ['appendix_type', 'position'])]
#[ORM\HasLifecycleCallbacks]
class AppendixStaticImage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::BIGINT)]
    private ?int $id = null;

    #[ORM\Column(length: 50, enumType: AppendixTypeEnum::class)]
    private AppendixTypeEnum $appendixType;

    /** Порядок отображения внутри приложения */
    #[ORM\Column(type: Types::INTEGER, options: ['default' => 0])]
    private int $position = 0;

    /** Имя файла на диске (хранится EasyAdmin ImageField) */
    #[ORM\Column(length: 255)]
    private string $fileName;

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

    public function getAppendixType(): AppendixTypeEnum
    {
        return $this->appendixType;
    }

    public function setAppendixType(AppendixTypeEnum $appendixType): static
    {
        $this->appendixType = $appendixType;
        return $this;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): static
    {
        $this->position = $position;
        return $this;
    }

    public function getFileName(): string
    {
        return $this->fileName;
    }

    public function setFileName(string $fileName): static
    {
        $this->fileName = $fileName;
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
}
