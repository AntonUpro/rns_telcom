<?php

declare(strict_types=1);

namespace App\Entity\Gauge;

use App\Repository\Gauge\GaugeAngleEqualRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Сортамент уголков равнополочных по ГОСТ 8509-93 (таблица gauge_angle_equal).
 *
 * profile_id — одновременно PRIMARY KEY и FOREIGN KEY на gauge_profile.id.
 * Это классическая связь «один к одному» с разделением таблицы:
 * общие реквизиты хранит GaugeProfile, геометрия и механика — этот класс.
 *
 * Единицы измерения:
 *   - геометрия сечения: мм
 *   - положение ЦТ, радиусы инерции: см
 *   - площадь: см²
 *   - моменты инерции: см⁴
 *   - моменты сопротивления: см³
 *   - масса: кг/м
 */
#[ORM\Entity(repositoryClass: GaugeAngleEqualRepository::class)]
#[ORM\Table(name: 'gauge_angle_equal')]
class GaugeAngleEqual
{
    /**
     * profile_id — PK и FK одновременно.
     * cascade: persist — при сохранении GaugeAngleEqual Doctrine автоматически
     * сохранит связанный GaugeProfile (INSERT в gauge_profile сделается первым).
     * cascade: remove — удаление уголка каскадно удалит запись в gauge_profile.
     */
    #[ORM\Id]
    #[ORM\OneToOne(targetEntity: GaugeProfile::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(name: 'profile_id', referencedColumnName: 'id', nullable: false)]
    private GaugeProfile $profile;

    // -------------------------------------------------------------------------
    // Геометрия поперечного сечения
    // -------------------------------------------------------------------------

    /** Ширина полки b, мм (обе полки равны для равнополочного уголка). */
    #[ORM\Column(name: 'flange_width', type: Types::DECIMAL, precision: 6, scale: 2)]
    private float $flangeWidth;

    /** Толщина полки t, мм. */
    #[ORM\Column(name: 'flange_thickness', type: Types::DECIMAL, precision: 5, scale: 2)]
    private float $flangeThickness;

    /** Радиус внутреннего закругления R, мм (у корня). */
    #[ORM\Column(name: 'inner_fillet_radius', type: Types::DECIMAL, precision: 5, scale: 2, nullable: true)]
    private ?float $innerFilletRadius = null;

    /** Радиус скругления кромки полки r, мм. */
    #[ORM\Column(name: 'edge_fillet_radius', type: Types::DECIMAL, precision: 5, scale: 2, nullable: true)]
    private ?float $edgeFilletRadius = null;

    // -------------------------------------------------------------------------
    // Положение центра тяжести
    // -------------------------------------------------------------------------

    /**
     * Расстояние от наружной грани полки до центра тяжести по оси x₀, см.
     * Для равнополочного уголка z₀x = z₀y.
     */
    #[ORM\Column(name: 'centroid_distance_x', type: Types::DECIMAL, precision: 5, scale: 2)]
    private float $centroidDistanceX;

    /** Расстояние от наружной грани полки до центра тяжести по оси y₀, см. */
    #[ORM\Column(name: 'centroid_distance_y', type: Types::DECIMAL, precision: 5, scale: 2)]
    private float $centroidDistanceY;

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

    /** Момент инерции относительно центральной оси x, см⁴. */
    #[ORM\Column(name: 'moment_inertia_x', type: Types::DECIMAL, precision: 10, scale: 2)]
    private float $momentInertiaX;

    /** Момент инерции относительно центральной оси y, см⁴ (= Ix для равнополочного). */
    #[ORM\Column(name: 'moment_inertia_y', type: Types::DECIMAL, precision: 10, scale: 2)]
    private float $momentInertiaY;

    /**
     * Главный максимальный момент инерции J₁ относительно оси u (под 45° к x/y), см⁴.
     * J₁ = Ix + |Jxy|.
     */
    #[ORM\Column(name: 'moment_inertia_max', type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?float $momentInertiaMax = null;

    /**
     * Главный минимальный момент инерции J₂ относительно оси v (под 45° к x/y), см⁴.
     * Критичен при расчёте устойчивости элементов. J₂ = Ix − |Jxy|.
     */
    #[ORM\Column(name: 'moment_inertia_min', type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?float $momentInertiaMin = null;

    /**
     * Центробежный момент инерции Jxy, см⁴.
     * Для равнополочного уголка Jxy < 0 (оси x, y не являются главными).
     */
    #[ORM\Column(name: 'centrifugal_moment_inertia', type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?float $centrifugalMomentInertia = null;

    // -------------------------------------------------------------------------
    // Радиусы инерции
    // -------------------------------------------------------------------------

    /** Радиус инерции относительно оси x: ix = √(Ix/A), см. */
    #[ORM\Column(name: 'radius_inertia_x', type: Types::DECIMAL, precision: 6, scale: 2)]
    private float $radiusInertiaX;

    /** Радиус инерции относительно оси y: iy = √(Iy/A), см. */
    #[ORM\Column(name: 'radius_inertia_y', type: Types::DECIMAL, precision: 6, scale: 2)]
    private float $radiusInertiaY;

    /**
     * Минимальный радиус инерции i_min = √(J₂/A), см.
     * Используется при расчёте гибкости и устойчивости сжатых элементов по СП 16.
     */
    #[ORM\Column(name: 'radius_inertia_min', type: Types::DECIMAL, precision: 6, scale: 2, nullable: true)]
    private ?float $radiusInertiaMin = null;

    // -------------------------------------------------------------------------
    // Моменты сопротивления
    // -------------------------------------------------------------------------

    /** Момент сопротивления Wx = Ix / (b − z₀x), см³. */
    #[ORM\Column(name: 'moment_resistance_x', type: Types::DECIMAL, precision: 8, scale: 2)]
    private float $momentResistanceX;

    /** Момент сопротивления Wy = Iy / (b − z₀y), см³. */
    #[ORM\Column(name: 'moment_resistance_y', type: Types::DECIMAL, precision: 8, scale: 2)]
    private float $momentResistanceY;

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

    public function getFlangeWidth(): float
    {
        return $this->flangeWidth;
    }

    public function setFlangeWidth(float $flangeWidth): static
    {
        $this->flangeWidth = $flangeWidth;
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

    public function getCentroidDistanceX(): float
    {
        return $this->centroidDistanceX;
    }

    public function setCentroidDistanceX(float $centroidDistanceX): static
    {
        $this->centroidDistanceX = $centroidDistanceX;
        return $this;
    }

    public function getCentroidDistanceY(): float
    {
        return $this->centroidDistanceY;
    }

    public function setCentroidDistanceY(float $centroidDistanceY): static
    {
        $this->centroidDistanceY = $centroidDistanceY;
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

    public function getMomentInertiaMax(): ?float
    {
        return $this->momentInertiaMax;
    }

    public function setMomentInertiaMax(?float $momentInertiaMax): static
    {
        $this->momentInertiaMax = $momentInertiaMax;
        return $this;
    }

    public function getMomentInertiaMin(): ?float
    {
        return $this->momentInertiaMin;
    }

    public function setMomentInertiaMin(?float $momentInertiaMin): static
    {
        $this->momentInertiaMin = $momentInertiaMin;
        return $this;
    }

    public function getCentrifugalMomentInertia(): ?float
    {
        return $this->centrifugalMomentInertia;
    }

    public function setCentrifugalMomentInertia(?float $centrifugalMomentInertia): static
    {
        $this->centrifugalMomentInertia = $centrifugalMomentInertia;
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

    public function getRadiusInertiaMin(): ?float
    {
        return $this->radiusInertiaMin;
    }

    public function setRadiusInertiaMin(?float $radiusInertiaMin): static
    {
        $this->radiusInertiaMin = $radiusInertiaMin;
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
}
