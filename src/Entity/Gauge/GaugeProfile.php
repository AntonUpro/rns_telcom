<?php

declare(strict_types=1);

namespace App\Entity\Gauge;

use App\Repository\Gauge\GaugeProfileRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Общий каталог стальных профилей (таблица gauge_profile).
 *
 * Хранит реквизиты, общие для любого типа профиля: наименование, обозначение
 * по ГОСТ, нормативный документ. Технические (геометрические, механические)
 * характеристики хранятся в дочерних таблицах:
 *   - gauge_angle_equal  → GaugeAngleEqual
 *   - gauge_channel      → GaugeChannel
 *   - gauge_i_beam       → GaugeIBeam
 *   - gauge_pipe_round   → GaugePipeRound
 *   - gauge_pipe_square  → GaugePipeSquare
 *   - gauge_round_solid  → GaugeRoundSolid
 *
 * Связь «один к одному»: profile_id в дочерней таблице — это одновременно
 * PRIMARY KEY и FOREIGN KEY на gauge_profile.id.
 */
#[ORM\Entity(repositoryClass: GaugeProfileRepository::class)]
#[ORM\Table(name: 'gauge_profile')]
#[ORM\Index(name: 'idx_gauge_profile_type_id', columns: ['type_id'])]
#[ORM\UniqueConstraint(name: 'uniq_gauge_profile_type_designation', columns: ['type_id', 'designation'])]
#[ORM\HasLifecycleCallbacks]
class GaugeProfile
{
    /** Суррогатный ключ (BIGSERIAL). */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::BIGINT)]
    private ?int $id = null;

    /**
     * Тип профиля — ссылка на справочник gauge_profile_type.
     * Через этот объект можно получить код типа (GaugeProfileTypeEnum).
     */
    #[ORM\ManyToOne(targetEntity: GaugeProfileType::class)]
    #[ORM\JoinColumn(name: 'type_id', referencedColumnName: 'id', nullable: false)]
    private GaugeProfileType $type;

    /**
     * Полное наименование профиля.
     * Пример: «Уголок 50×50×5», «Швеллер №10», «Двутавр 20Б1».
     */
    #[ORM\Column(name: 'name', length: 255)]
    private string $name;

    /**
     * ГОСТовское обозначение (designation).
     * Пример: «L50×5» для уголка, «10» для швеллера, «I20» для двутавра.
     * Уникально в рамках одного типа профиля (type_id + designation).
     */
    #[ORM\Column(name: 'designation', length: 50)]
    private string $designation;

    /**
     * Нормативный документ, по которому выпускается профиль.
     * Пример: «ГОСТ 8509-93», «ГОСТ 8240-97».
     */
    #[ORM\Column(name: 'standard', length: 255, nullable: true)]
    private ?string $standard = null;

    /**
     * Признак нестандартного (пользовательского) профиля.
     * FALSE — стандартный профиль из сортамента ГОСТ.
     * TRUE  — профиль добавлен вручную пользователем.
     */
    #[ORM\Column(name: 'is_custom', type: Types::BOOLEAN)]
    private bool $isCustom = false;

    #[ORM\Column(name: 'created_at', type: Types::DATETIME_IMMUTABLE)]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'updated_at', type: Types::DATETIME_IMMUTABLE)]
    private DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    /** Автоматически обновляет updated_at перед каждым UPDATE. */
    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): GaugeProfileType
    {
        return $this->type;
    }

    public function setType(GaugeProfileType $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getDesignation(): string
    {
        return $this->designation;
    }

    public function setDesignation(string $designation): static
    {
        $this->designation = $designation;
        return $this;
    }

    public function getStandard(): ?string
    {
        return $this->standard;
    }

    public function setStandard(?string $standard): static
    {
        $this->standard = $standard;
        return $this;
    }

    public function isCustom(): bool
    {
        return $this->isCustom;
    }

    public function setIsCustom(bool $isCustom): static
    {
        $this->isCustom = $isCustom;
        return $this;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
