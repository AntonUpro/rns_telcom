<?php

declare(strict_types=1);

namespace App\Entity\Gauge;

use App\Repository\Gauge\GaugeIBeamRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Сортамент двутавров по ГОСТ 8239-89 (таблица gauge_i_beam).
 *
 * Сечение двутавра симметрично относительно обеих осей (x и y).
 * Ось x — сильная (большой момент инерции), ось y — слабая.
 * Статический момент Sx используется в расчёте касательных напряжений
 * в стенке от поперечной силы Q.
 *
 * Единицы: геометрия — мм, радиусы/ЦТ — см, площадь — см²,
 *           моменты инерции — см⁴, моменты сопротивления — см³, масса — кг/м.
 */
#[ORM\Entity(repositoryClass: GaugeIBeamRepository::class)]
#[ORM\Table(name: 'gauge_i_beam')]
class GaugeIBeam
{
    #[ORM\Id]
    #[ORM\OneToOne(targetEntity: GaugeProfile::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(name: 'profile_id', referencedColumnName: 'id', nullable: false)]
    private GaugeProfile $profile;

    // -------------------------------------------------------------------------
    // Геометрия поперечного сечения
    // -------------------------------------------------------------------------

    /** Высота профиля h, мм. */
    #[ORM\Column(name: 'height', type: Types::DECIMAL, precision: 6, scale: 2)]
    private float $height;

    /** Ширина полки b, мм. */
    #[ORM\Column(name: 'flange_width', type: Types::DECIMAL, precision: 6, scale: 2)]
    private float $flangeWidth;

    /** Толщина стенки s, мм. */
    #[ORM\Column(name: 'web_thickness', type: Types::DECIMAL, precision: 5, scale: 2)]
    private float $webThickness;

    /** Толщина полки t, мм. */
    #[ORM\Column(name: 'flange_thickness', type: Types::DECIMAL, precision: 5, scale: 2)]
    private float $flangeThickness;

    /** Радиус внутреннего закругления R, мм. */
    #[ORM\Column(name: 'inner_fillet_radius', type: Types::DECIMAL, precision: 5, scale: 2, nullable: true)]
    private ?float $innerFilletRadius = null;

    /** Радиус скругления кромки полки r, мм. */
    #[ORM\Column(name: 'edge_fillet_radius', type: Types::DECIMAL, precision: 5, scale: 2, nullable: true)]
    private ?float $edgeFilletRadius = null;

    // -------------------------------------------------------------------------
    // Площадь и масса
    // -------------------------------------------------------------------------

    /** Площадь поперечного сечения A, см². */
    #[ORM\Column(name: 'area', type: Types::DECIMAL, precision: 8, scale: 3)]
    private float $area;

    /** Теоретическая масса 1 м профиля, кг/м. */
    #[ORM\Column(name: 'mass_per_meter', type: Types::DECIMAL, precision: 6, scale: 3, nullable: true)]
    private ?float $massPerMeter = null;

    // -------------------------------------------------------------------------
    // Моменты инерции
    // -------------------------------------------------------------------------

    /**
     * Момент инерции относительно оси x (сильная ось, горизонтальная), см⁴.
     * Используется для расчёта прогиба и прочности при изгибе в плоскости стенки.
     */
    #[ORM\Column(name: 'moment_inertia_x', type: Types::DECIMAL, precision: 12, scale: 2)]
    private float $momentInertiaX;

    /**
     * Момент инерции относительно оси y (слабая ось, вертикальная), см⁴.
     * Используется при расчёте устойчивости из плоскости и при изгибе из плоскости.
     */
    #[ORM\Column(name: 'moment_inertia_y', type: Types::DECIMAL, precision: 10, scale: 2)]
    private float $momentInertiaY;

    // -------------------------------------------------------------------------
    // Радиусы инерции
    // -------------------------------------------------------------------------

    /** Радиус инерции ix = √(Ix/A), см. */
    #[ORM\Column(name: 'radius_inertia_x', type: Types::DECIMAL, precision: 6, scale: 2)]
    private float $radiusInertiaX;

    /** Радиус инерции iy = √(Iy/A), см. */
    #[ORM\Column(name: 'radius_inertia_y', type: Types::DECIMAL, precision: 6, scale: 2)]
    private float $radiusInertiaY;

    // -------------------------------------------------------------------------
    // Моменты сопротивления
    // -------------------------------------------------------------------------

    /** Момент сопротивления Wx = Ix / (h/2), см³. */
    #[ORM\Column(name: 'moment_resistance_x', type: Types::DECIMAL, precision: 10, scale: 2)]
    private float $momentResistanceX;

    /** Момент сопротивления Wy = Iy / (b/2), см³. */
    #[ORM\Column(name: 'moment_resistance_y', type: Types::DECIMAL, precision: 8, scale: 2)]
    private float $momentResistanceY;

    // -------------------------------------------------------------------------
    // Статический момент
    // -------------------------------------------------------------------------

    /**
     * Статический момент полусечения Sx, см³.
     * τ_max = Q·Sx / (Ix·s) — максимальное касательное напряжение в стенке.
     */
    #[ORM\Column(name: 'static_moment_x', type: Types::DECIMAL, precision: 10, scale: 2)]
    private float $staticMomentX;

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

    public function getHeight(): float
    {
        return $this->height;
    }

    public function setHeight(float $height): static
    {
        $this->height = $height;
        return $this;
    }

    public function getFlangeWidth(): float
    {
        return $this->flangeWidth;
    }

    public function setFlangeWidth(float $flangeWidth): static
    {
        $this->flangeWidth = $flangeWidth;
        return $this;
    }

    public function getWebThickness(): float
    {
        return $this->webThickness;
    }

    public function setWebThickness(float $webThickness): static
    {
        $this->webThickness = $webThickness;
        return $this;
    }

    public function getFlangeThickness(): float
    {
        return $this->flangeThickness;
    }

    public function setFlangeThickness(float $flangeThickness): static
    {
        $this->flangeThickness = $flangeThickness;
        return $this;
    }

    public function getInnerFilletRadius(): ?float
    {
        return $this->innerFilletRadius;
    }

    public function setInnerFilletRadius(?float $innerFilletRadius): static
    {
        $this->innerFilletRadius = $innerFilletRadius;
        return $this;
    }

    public function getEdgeFilletRadius(): ?float
    {
        return $this->edgeFilletRadius;
    }

    public function setEdgeFilletRadius(?float $edgeFilletRadius): static
    {
        $this->edgeFilletRadius = $edgeFilletRadius;
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

    public function getMomentInertiaX(): float
    {
        return $this->momentInertiaX;
    }

    public function setMomentInertiaX(float $momentInertiaX): static
    {
        $this->momentInertiaX = $momentInertiaX;
        return $this;
    }

    public function getMomentInertiaY(): float
    {
        return $this->momentInertiaY;
    }

    public function setMomentInertiaY(float $momentInertiaY): static
    {
        $this->momentInertiaY = $momentInertiaY;
        return $this;
    }

    public function getRadiusInertiaX(): float
    {
        return $this->radiusInertiaX;
    }

    public function setRadiusInertiaX(float $radiusInertiaX): static
    {
        $this->radiusInertiaX = $radiusInertiaX;
        return $this;
    }

    public function getRadiusInertiaY(): float
    {
        return $this->radiusInertiaY;
    }

    public function setRadiusInertiaY(float $radiusInertiaY): static
    {
        $this->radiusInertiaY = $radiusInertiaY;
        return $this;
    }

    public function getMomentResistanceX(): float
    {
        return $this->momentResistanceX;
    }

    public function setMomentResistanceX(float $momentResistanceX): static
    {
        $this->momentResistanceX = $momentResistanceX;
        return $this;
    }

    public function getMomentResistanceY(): float
    {
        return $this->momentResistanceY;
    }

    public function setMomentResistanceY(float $momentResistanceY): static
    {
        $this->momentResistanceY = $momentResistanceY;
        return $this;
    }

    public function getStaticMomentX(): float
    {
        return $this->staticMomentX;
    }

    public function setStaticMomentX(float $staticMomentX): static
    {
        $this->staticMomentX = $staticMomentX;
        return $this;
    }
}
