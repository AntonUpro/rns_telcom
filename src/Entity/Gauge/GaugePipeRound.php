<?php

declare(strict_types=1);

namespace App\Entity\Gauge;

use App\Repository\Gauge\GaugePipeRoundRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Сортамент труб электросварных (ГОСТ 10704-91) и
 * холоднодеформированных (ГОСТ 8734-75) (таблица gauge_pipe_round).
 *
 * Круглое кольцевое сечение: Ix = Iy, поэтому хранится одно значение.
 * Пластический момент сопротивления Wpl используется при расчёте
 * по предельным состояниям второго рода (несущая способность сечения).
 *
 * Единицы: геометрия — мм, радиусы — см, площадь — см²,
 *           момент инерции — см⁴, моменты сопротивления — см³, масса — кг/м.
 */
#[ORM\Entity(repositoryClass: GaugePipeRoundRepository::class)]
#[ORM\Table(name: 'gauge_pipe_round')]
class GaugePipeRound
{
    #[ORM\Id]
    #[ORM\OneToOne(targetEntity: GaugeProfile::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(name: 'profile_id', referencedColumnName: 'id', nullable: false)]
    private GaugeProfile $profile;

    // -------------------------------------------------------------------------
    // Геометрия поперечного сечения
    // -------------------------------------------------------------------------

    /** Наружный диаметр трубы D, мм. */
    #[ORM\Column(name: 'outer_diameter', type: Types::DECIMAL, precision: 7, scale: 2)]
    private float $outerDiameter;

    /** Толщина стенки t, мм. */
    #[ORM\Column(name: 'wall_thickness', type: Types::DECIMAL, precision: 5, scale: 2)]
    private float $wallThickness;

    // -------------------------------------------------------------------------
    // Площадь и масса
    // -------------------------------------------------------------------------

    /**
     * Площадь поперечного сечения A = π/4·(D² − d²), см².
     * d = D − 2t — внутренний диаметр.
     */
    #[ORM\Column(name: 'area', type: Types::DECIMAL, precision: 8, scale: 3)]
    private float $area;

    /** Теоретическая масса 1 м трубы, кг/м. */
    #[ORM\Column(name: 'mass_per_meter', type: Types::DECIMAL, precision: 6, scale: 3, nullable: true)]
    private ?float $massPerMeter = null;

    // -------------------------------------------------------------------------
    // Осевые характеристики (Ix = Iy для кольцевого сечения)
    // -------------------------------------------------------------------------

    /**
     * Осевой момент инерции Ix = Iy = π/64·(D⁴ − d⁴), см⁴.
     * Одно значение — сечение осесимметрично.
     */
    #[ORM\Column(name: 'moment_inertia', type: Types::DECIMAL, precision: 10, scale: 2)]
    private float $momentInertia;

    /** Радиус инерции i = √(I/A), см. */
    #[ORM\Column(name: 'radius_inertia', type: Types::DECIMAL, precision: 6, scale: 2)]
    private float $radiusInertia;

    /** Осевой момент сопротивления Wx = Wy = I / (D/2), см³. */
    #[ORM\Column(name: 'moment_resistance', type: Types::DECIMAL, precision: 8, scale: 2)]
    private float $momentResistance;

    /**
     * Пластический момент сопротивления Wpl, см³.
     * Wpl = (D³ − d³) / 6. Применяется при расчёте по предельным состояниям.
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

    public function getOuterDiameter(): float
    {
        return $this->outerDiameter;
    }

    public function setOuterDiameter(float $outerDiameter): static
    {
        $this->outerDiameter = $outerDiameter;
        return $this;
    }

    public function getWallThickness(): float
    {
        return $this->wallThickness;
    }

    public function setWallThickness(float $wallThickness): static
    {
        $this->wallThickness = $wallThickness;
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
