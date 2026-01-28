<?php
// src/Entity/CalculationData.php

namespace App\Entity;

use App\Entity\JsonData\AbstractJsonData;
use App\Entity\JsonData\ConcretePillarSpecificData;
use App\Enum\CalculationData\IcingRegionEnum;
use App\Enum\CalculationData\SnowRegionEnum;
use App\Enum\CalculationData\TerrainTypeEnum;
use App\Enum\CalculationData\WindRegionEnum;
use App\Repository\CalculationDataRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use DateTimeImmutable;

#[ORM\Entity(repositoryClass: CalculationDataRepository::class)]
#[ORM\Table(name: 'calculation_data')]
#[ORM\HasLifecycleCallbacks]
class CalculationData
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'calculationData', targetEntity: Calculation::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Calculation $calculation = null;

    // Основные данные объекта
    #[ORM\Column(length: 50, nullable: true)]
    #[Assert\Length(max: 50)]
    private ?string $objectCode = null;

    // номер базовой станции
    #[ORM\Column(length: 50, nullable: true)]
    #[Assert\Length(max: 50)]
    private ?string $baseStationNumber = null;

    // Область
    #[ORM\Column(length: 100, nullable: true)]
    #[Assert\Length(max: 100)]
    private ?string $region = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Assert\Length(max: 100)]
    private ?string $locality = null;

    // Заказчик
    #[ORM\Column(length: 150, nullable: true)]
    #[Assert\Length(max: 150)]
    private ?string $customer = null;

    // Тип амс
    #[ORM\Column(length: 100, nullable: true)]
    #[Assert\Length(max: 100)]
    private ?string $amsType = null;

    // Высота амс
    #[ORM\Column(type: Types::DECIMAL, precision: 6, scale: 2, nullable: true)]
    #[Assert\Range(min: 0, max: 1000)]
    private ?string $amsHeight = null;

    // Дата обследования
    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $surveyDate = null;

    // Климатические параметры
    #[ORM\Column(length: 10, nullable: true)]
    #[Assert\Choice(callback: 'getAvailableWindRegions')]
    private ?WindRegionEnum $windRegion = null;

    #[ORM\Column(length: 5, nullable: true)]
    #[Assert\Choice(callback: 'getAvailableTerrainTypes')]
    private ?TerrainTypeEnum $terrainType = null;

    #[ORM\Column(length: 10, nullable: true)]
    #[Assert\Choice(callback: 'getAvailableSnowRegions')]
    private ?SnowRegionEnum $snowRegion = null;

    #[ORM\Column(length: 10, nullable: true)]
    #[Assert\Choice(callback: 'getAvailableIcingRegions')]
    private ?IcingRegionEnum $icingRegion = null;

    // Координаты
    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 7, nullable: true)]
    #[Assert\Range(min: -90, max: 90)]
    private ?string $latitude = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 7, nullable: true)]
    #[Assert\Range(min: -180, max: 180)]
    private ?string $longitude = null;

    // Параметры для конкретных типов расчетов (хранятся в JSON)
    #[ORM\Column(name: 'specific_data', type: Types::JSON, nullable: true)]
    private array $specificData = [];

    private ?AbstractJsonData $specificDataObject = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
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

    public function setCalculation(Calculation $calculation): static
    {
        $this->calculation = $calculation;

        return $this;
    }

    // Геттеры и сеттеры для основных полей
    public function getObjectCode(): ?string
    {
        return $this->objectCode;
    }

    public function setObjectCode(?string $objectCode): static
    {
        $this->objectCode = $objectCode;

        return $this;
    }

    public function getBaseStationNumber(): ?string
    {
        return $this->baseStationNumber;
    }

    public function setBaseStationNumber(?string $baseStationNumber): static
    {
        $this->baseStationNumber = $baseStationNumber;

        return $this;
    }

    public function getRegion(): ?string
    {
        return $this->region;
    }

    public function setRegion(?string $region): static
    {
        $this->region = $region;

        return $this;
    }

    public function getLocality(): ?string
    {
        return $this->locality;
    }

    public function setLocality(?string $locality): static
    {
        $this->locality = $locality;

        return $this;
    }

    public function getCustomer(): ?string
    {
        return $this->customer;
    }

    public function setCustomer(?string $customer): static
    {
        $this->customer = $customer;

        return $this;
    }

    public function getAmsType(): ?string
    {
        return $this->amsType;
    }

    public function setAmsType(?string $amsType): static
    {
        $this->amsType = $amsType;

        return $this;
    }

    public function getAmsHeight(): ?string
    {
        return $this->amsHeight;
    }

    public function setAmsHeight(?string $amsHeight): static
    {
        $this->amsHeight = $amsHeight;

        return $this;
    }

    public function getSurveyDate(): ?\DateTimeInterface
    {
        return $this->surveyDate;
    }

    public function setSurveyDate(?\DateTimeInterface $surveyDate): static
    {
        $this->surveyDate = $surveyDate;

        return $this;
    }

    public function getWindRegion(): ?WindRegionEnum
    {
        return $this->windRegion;
    }

    public function setWindRegion(?WindRegionEnum $windRegion): static
    {
        $this->windRegion = $windRegion;

        return $this;
    }

    public function getTerrainType(): ?TerrainTypeEnum
    {
        return $this->terrainType;
    }

    public function setTerrainType(?TerrainTypeEnum $terrainType): static
    {
        $this->terrainType = $terrainType;

        return $this;
    }

    public function getSnowRegion(): ?SnowRegionEnum
    {
        return $this->snowRegion;
    }

    public function setSnowRegion(?SnowRegionEnum $snowRegion): static
    {
        $this->snowRegion = $snowRegion;

        return $this;
    }

    public function getIcingRegion(): ?IcingRegionEnum
    {
        return $this->icingRegion;
    }

    public function setIcingRegion(?IcingRegionEnum $icingRegion): static
    {
        $this->icingRegion = $icingRegion;

        return $this;
    }

    public function getLatitude(): ?string
    {
        return $this->latitude;
    }

    public function setLatitude(?string $latitude): static
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getLongitude(): ?string
    {
        return $this->longitude;
    }

    public function setLongitude(?string $longitude): static
    {
        $this->longitude = $longitude;

        return $this;
    }

    // Методы для данных конкретных типов расчетов
    public function getSpecificData(): array
    {
        return $this->specificData;
    }

    public function setSpecificData(array $concretePillarData): static
    {
        $this->specificData = $concretePillarData;

        return $this;
    }

    public function getConcretePillarSpecificData(): ?ConcretePillarSpecificData
    {
        if ($this->specificDataObject === null) {
            $this->specificDataObject = ConcretePillarSpecificData::fromArray($this->specificData);
        }

        return $this->specificDataObject;
    }

    public function setSpecificDataObject(?AbstractJsonData $specificDataObject): static
    {
        $this->specificDataObject = $specificDataObject;
        $this->specificData = $specificDataObject?->toArray();

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
            'calculation_id' => $this->calculation?->getId(),
            'objectCode' => $this->objectCode,
            'baseStationNumber' => $this->baseStationNumber,
            'region' => $this->region,
            'locality' => $this->locality,
            'customer' => $this->customer,
            'amsType' => $this->amsType,
            'amsHeight' => $this->amsHeight,
            'surveyDate' => $this->surveyDate?->format('Y-m-d'),
            'windRegion' => $this->windRegion?->value,
            'terrainType' => $this->terrainType?->value,
            'snowRegion' => $this->snowRegion?->value,
            'icingRegion' => $this->icingRegion?->value,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'specificData' => $this->specificData,
            'createdAt' => $this->createdAt->format('Y-m-d H:i:s'),
            'updatedAt' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
