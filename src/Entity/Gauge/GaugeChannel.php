<?php

declare(strict_types=1);

namespace App\Entity\Gauge;

use App\Repository\Gauge\GaugeChannelRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Сортамент швеллеров по ГОСТ 8240-97 (таблица gauge_channel).
 *
 * Сечение несимметрично относительно оси y: центр тяжести смещён
 * от наружной грани стенки на расстояние z₀. Это порождает два значения
 * момента сопротивления Wy: к дальней грани полки (Wy) и к грани стенки (W'y).
 *
 * Единицы: геометрия — мм, ЦТ/радиусы — см, площадь — см²,
 *           моменты инерции — см⁴, моменты сопротивления — см³, масса — кг/м.
 */
#[ORM\Entity(repositoryClass: GaugeChannelRepository::class)]
#[ORM\Table(name: 'gauge_channel')]
class GaugeChannel
{
    /**
     * profile_id — PK и FK одновременно.
     * cascade persist/remove: Doctrine сначала сохранит GaugeProfile,
     * затем эту запись; при удалении — наоборот.
     */
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

    /** Средняя толщина полки t, мм. */
    #[ORM\Column(name: 'flange_thickness', type: Types::DECIMAL, precision: 5, scale: 2)]
    private float $flangeThickness;

    /** Радиус внутреннего закругления R, мм (у сопряжения стенки с полкой). */
    #[ORM\Column(name: 'inner_fillet_radius', type: Types::DECIMAL, precision: 5, scale: 2, nullable: true)]
    private ?float $innerFilletRadius = null;

    /** Радиус скругления кромки полки r, мм. */
    #[ORM\Column(name: 'edge_fillet_radius', type: Types::DECIMAL, precision: 5, scale: 2, nullable: true)]
    private ?float $edgeFilletRadius = null;

    // -------------------------------------------------------------------------
    // Положение центра тяжести
    // -------------------------------------------------------------------------

    /**
     * Расстояние от наружной грани стенки до центра тяжести z₀, см.
     * Сечение несимметрично относительно оси y, поэтому z₀ ≠ b/2.
     */
    #[ORM\Column(name: 'centroid_distance_z', type: Types::DECIMAL, precision: 5, scale: 2)]
    private float $centroidDistanceZ;

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

    /** Момент инерции относительно горизонтальной оси x (сильная ось), см⁴. */
    #[ORM\Column(name: 'moment_inertia_x', type: Types::DECIMAL, precision: 10, scale: 2)]
    private float $momentInertiaX;

    /** Момент инерции относительно вертикальной оси y (слабая ось), см⁴. */
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

    /**
     * Момент сопротивления Wx = Ix / (h/2), см³.
     * Сечение симметрично по оси x, поэтому одно значение.
     */
    #[ORM\Column(name: 'moment_resistance_x', type: Types::DECIMAL, precision: 8, scale: 2)]
    private float $momentResistanceX;

    /**
     * Момент сопротивления Wy = Iy / (b − z₀), см³.
     * К дальней от ЦТ грани полки. Меньшее значение — определяет прочность.
     */
    #[ORM\Column(name: 'moment_resistance_y', type: Types::DECIMAL, precision: 8, scale: 2)]
    private float $momentResistanceY;

    /**
     * Момент сопротивления W'y = Iy / z₀, см³.
     * К ближней грани (наружная грань стенки). Большее значение.
     */
    #[ORM\Column(name: 'moment_resistance_y_near', type: Types::DECIMAL, precision: 8, scale: 2, nullable: true)]
    private ?float $momentResistanceYNear = null;

    // -------------------------------------------------------------------------
    // Статический момент
    // -------------------------------------------------------------------------

    /**
     * Статический момент полусечения относительно оси x, Sx = Ix / (h/2), см³.
     * Применяется при расчёте касательных напряжений: τ = Q·Sx / (Ix·s).
     */
    #[ORM\Column(name: 'static_moment_x', type: Types::DECIMAL, precision: 8, scale: 2)]
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

    public function getCentroidDistanceZ(): float
    {
        return $this->centroidDistanceZ;
    }

    public function setCentroidDistanceZ(float $centroidDistanceZ): static
    {
        $this->centroidDistanceZ = $centroidDistanceZ;
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

    public function getMomentResistanceYNear(): ?float
    {
        return $this->momentResistanceYNear;
    }

    public function setMomentResistanceYNear(?float $momentResistanceYNear): static
    {
        $this->momentResistanceYNear = $momentResistanceYNear;
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
