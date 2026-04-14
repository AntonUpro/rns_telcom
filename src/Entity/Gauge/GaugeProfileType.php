<?php

declare(strict_types=1);

namespace App\Entity\Gauge;

use App\Enum\Gauge\GaugeProfileTypeEnum;
use App\Repository\Gauge\GaugeProfileTypeRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Справочник типов стальных профилей.
 * Таблица gauge_profile_type — неизменяемый справочник, заполняется миграцией.
 *
 * Пример записей:
 *   id=1, code=ANGLE_EQUAL, name='Уголок равнополочный'
 *   id=3, code=CHANNEL,     name='Швеллер'
 */
#[ORM\Entity(repositoryClass: GaugeProfileTypeRepository::class)]
#[ORM\Table(name: 'gauge_profile_type')]
class GaugeProfileType
{
    /** Идентификатор типа профиля (SERIAL в БД). */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    /**
     * Машинный код типа: ANGLE_EQUAL, CHANNEL, I_BEAM и т.д.
     * Используется как дискриминатор при работе с сортаментами.
     */
    #[ORM\Column(name: 'code', length: 32, unique: true, enumType: GaugeProfileTypeEnum::class)]
    private GaugeProfileTypeEnum $code;

    /** Человекочитаемое наименование типа профиля, напр. «Швеллер». */
    #[ORM\Column(name: 'name', length: 255)]
    private string $name;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): GaugeProfileTypeEnum
    {
        return $this->code;
    }

    public function setCode(GaugeProfileTypeEnum $code): static
    {
        $this->code = $code;
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
}
