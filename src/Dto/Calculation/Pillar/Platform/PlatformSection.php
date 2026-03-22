<?php

declare(strict_types=1);

namespace App\Dto\Calculation\Pillar\Platform;

class PlatformSection
{

    /**
     * @param Element[] $elements
     */
    public function __construct(
        public ?int $id,
        public int $height,
        public int $widthBottom,
        public int $widthTop,
        public array $elements
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            height: (int) $data['height'],
            widthBottom: (int) $data['widthBottom'],
            widthTop: (int) $data['widthTop'],
            elements: array_map(fn(array $element) => Element::fromArray($element), $data['elements'])
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'height' => $this->height,
            'widthBottom' => $this->widthBottom,
            'widthTop' => $this->widthTop,
            'elements' => array_map(fn(Element $element) => $element->toArray(), $this->elements)
        ];
    }
}
