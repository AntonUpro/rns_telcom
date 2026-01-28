<?php

declare(strict_types=1);

namespace App\Dto\Calculation;

use Symfony\Component\Validator\Constraints as Assert;

class PillarDataDto
{
    public function __construct(
        #[Assert\Length(max: 100)]
        public ?string $pillarStamp = null,

        #[Assert\Type('bool')]
        public ?bool $strengtheningExist = false,

        #[Assert\Length(max: 50)]
        public ?string $strengtheningGeometry = null,

        #[Assert\Type('numeric')]
        #[Assert\Range(min: 0)]
        public ?float $strengtheningWidth = null,

        #[Assert\Type('numeric')]
        #[Assert\Range(min: 0)]
        public ?float $strengtheningHeight = null,

        #[Assert\Type('numeric')]
        #[Assert\Range(min: 0)]
        public ?float $allowedMoment = null,
    ) {
        // Если укрепление не существует, сбрасываем связанные поля
        if ($this->strengtheningExist === false) {
            $this->strengtheningGeometry = null;
            $this->strengtheningWidth = null;
            $this->strengtheningHeight = null;
        }
    }

    public function toArray(): array
    {
        return [
            'pillarStamp' => $this->pillarStamp,
            'strengtheningExist' => $this->strengtheningExist,
            'strengtheningGeometry' => $this->strengtheningGeometry,
            'strengtheningWidth' => $this->strengtheningWidth,
            'strengtheningHeight' => $this->strengtheningHeight,
            'allowedMoment' => $this->allowedMoment,
        ];
    }

    public static function fromArray(array $data): self
    {
        $strengtheningExist = isset($data['strengtheningExist'])
            ? filter_var($data['strengtheningExist'], FILTER_VALIDATE_BOOLEAN)
            : false;

        return new self(
            pillarStamp: $data['pillarStamp'] ?? null,
            strengtheningExist: $strengtheningExist,
            strengtheningGeometry: $data['strengtheningGeometry'] ?? null,
            strengtheningWidth: isset($data['strengtheningWidth']) ? (float) $data['strengtheningWidth'] : null,
            strengtheningHeight: isset($data['strengtheningHeight']) ? (float) $data['strengtheningHeight'] : null,
            allowedMoment: isset($data['allowedMoment']) ? (float) $data['allowedMoment'] : null,
        );
    }

    public function isEmpty(): bool
    {
        $array = $this->toArray();
        // Игнорируем булево поле strengtheningExist при проверке на пустоту
        unset($array['strengtheningExist']);
        return empty(array_filter($array, fn($value) => $value !== null));
    }

    public function hasStrengthening(): bool
    {
        return $this->strengtheningExist === true;
    }

    public function getStrengtheningArea(): ?float
    {
        if (!$this->hasStrengthening() || !$this->strengtheningWidth || !$this->strengtheningHeight) {
            return null;
        }

        return $this->strengtheningWidth * $this->strengtheningHeight;
    }
}
