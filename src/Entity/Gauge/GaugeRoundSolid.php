<?php

declare(strict_types=1);

namespace App\Entity\Gauge;

use App\Repository\Gauge\GaugeRoundSolidRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Сортамент круглого проката (сплошной круг) по ГОСТ 2590-2006
 * (таблица gauge_round_solid).
 *
 * Сплошное круглое сечение — все осевые характеристики Ix = Iy.
 * Полярные характеристики Ip и Wp используются при расчёте на кручение.
 * Пластический момент Wpl — при расчёте по предельным состояниям (СП 16).
 *
 * Все формулы через диаметр d (в мм в БД, в расчётах переводить в см):
 *   A   = π·d²/4
 *   Ix  = π·d⁴/64
 *   i   = d/4
 *   Wx  = π·d³/32
 *   Ip  = π·d⁴/32 = 2·Ix
 *   Wp  = π·d³/16
 *   Wpl = d³/6
 *
 * Единицы: геометрия — мм, радиусы — см, площадь — см²,
 *           моменты инерции — см⁴, моменты сопротивления — см³, масса — кг/м.
 */
#[ORM\Entity(repositoryClass: GaugeRoundSolidRepository::class)]
#[ORM\Table(name: 'gauge_round_solid')]
class GaugeRoundSolid
{
    #[ORM\Id]
    #[ORM\OneToOne(targetEntity: GaugeProfile::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(name: 'profile_id', referencedColumnName: 'id', nullable: false)]
    private GaugeProfile $profile;

    // -------------------------------------------------------------------------
    // Геометрия поперечного сечения
    // -------------------------------------------------------------------------

    /** Диаметр сплошного круга d, мм. */
    #[ORM\Column(name: 'diameter', type: Types::DECIMAL, precision: 7, scale: 2)]
    private float $diameter;

    // -------------------------------------------------------------------------
    // Площадь и масса
    // -------------------------------------------------------------------------

    /** Площадь поперечного сечения A = π·d²/4, см². */
    #[ORM\Column(name: 'area', type: Types::DECIMAL, precision: 8, scale: 3)]
    private float $area;

    /** Теоретическая масса 1 м проката, кг/м. */
    #[ORM\Column(name: 'mass_per_meter', type: Types::DECIMAL, precision: 6, scale: 3, nullable: true)]
    private ?float $massPerMeter = null;

    // -------------------------------------------------------------------------
    // Осевые характеристики (Ix = Iy)
    // -------------------------------------------------------------------------

    /** Осевой момент инерции Ix = Iy = π·d⁴/64, см⁴. */
    #[ORM\Column(name: 'moment_inertia', type: Types::DECIMAL, precision: 10, scale: 2)]
    private float $momentInertia;

    /** Радиус инерции i = d/4, см. */
    #[ORM\Column(name: 'radius_inertia', type: Types::DECIMAL, precision: 6, scale: 2)]
    private float $radiusInertia;

    /** Осевой момент сопротивления Wx = Wy = π·d³/32, см³. */
    #[ORM\Column(name: 'moment_resistance', type: Types::DECIMAL, precision: 8, scale: 2)]
    private float $momentResistance;

    // -------------------------------------------------------------------------
    // Полярные характеристики (для расчёта на кручение)
    // -------------------------------------------------------------------------

    /**
     * Полярный момент инерции Ip = π·d⁴/32 = 2·Ix, см⁴.
     * Применяется в расчётах на кручение: τ = M_кр·r / Ip.
     */
    #[ORM\Column(name: 'polar_moment_inertia', type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?float $polarMomentInertia = null;

    /**
     * Полярный момент сопротивления Wp = π·d³/16, см³.
     * Wp = Ip / (d/2). Используется при проверке прочности на кручение.
     */
    #[ORM\Column(name: 'polar_moment_resistance', type: Types::DECIMAL, precision: 8, scale: 2, nullable: true)]
    private ?float $polarMomentResistance = null;

    // -------------------------------------------------------------------------
    // Пластические характеристики
    // -------------------------------------------------------------------------

    /**
     * Пластический момент сопротивления Wpl = d³/6, см³.
     * Применяется при расчёте несущей способности сечения по СП 16.13330.
     */
    #[ORM\Column(name: 'plastic_moment_resistance', type: Types::DECIMAL, precision: 8, scale: 2, nullable: true)]
    private ?float $plasticMomentResistance = null;

    // -------------------------------------------------------------------------
    // Геттеры и сеттеры
    // -------------------------------------------------------------------------

    public function getProfile(): GaugeProfile
    {
        return $this->profile;
    }

    public function setProfile(GaugeProfile $profile): static
    {
        $this->profile = $profile;
        return $this;
    }

    public function getDiameter(): float
    {
        return $this->diameter;
    }

    public function setDiameter(float $diameter): static
    {
        $this->diameter = $diameter;
        return $this;
    }

    public function getArea(): float
    {
        return $this->area;
    }

    public function setArea(float $area): static
    {
        $this->area = $area;
        return $this;
    }

    public function getMassPerMeter(): ?float
    {
        return $this->massPerMeter;
    }

    public function setMassPerMeter(?float $massPerMeter): static
    {
        $this->massPerMeter = $massPerMeter;
        return $this;
    }

    public function getMomentInertia(): float
    {
        return $this->momentInertia;
    }

    public function setMomentInertia(float $momentInertia): static
    {
        $this->momentInertia = $momentInertia;
        return $this;
    }

    public function getRadiusInertia(): float
    {
        return $this->radiusInertia;
    }

    public function setRadiusInertia(float $radiusInertia): static
    {
        $this->radiusInertia = $radiusInertia;
        return $this;
    }

    public function getMomentResistance(): float
    {
        return $this->momentResistance;
    }

    public function setMomentResistance(float $momentResistance): static
    {
        $this->momentResistance = $momentResistance;
        return $this;
    }

    public function getPolarMomentInertia(): ?float
    {
        return $this->polarMomentInertia;
    }

    public function setPolarMomentInertia(?float $polarMomentInertia): static
    {
        $this->polarMomentInertia = $polarMomentInertia;
        return $this;
    }

    public function getPolarMomentResistance(): ?float
    {
        return $this->polarMomentResistance;
    }

    public function setPolarMomentResistance(?float $polarMomentResistance): static
    {
        $this->polarMomentResistance = $polarMomentResistance;
        return $this;
    }

    public function getPlasticMomentResistance(): ?float
    {
        return $this->plasticMomentResistance;
    }

    public function setPlasticMomentResistance(?float $plasticMomentResistance): static
    {
        $this->plasticMomentResistance = $plasticMomentResistance;
        return $this;
    }
}
