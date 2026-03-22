<?php

declare(strict_types=1);

namespace App\Dto\Calculation\Pillar\Platform;

class Element
{
    public function __construct(
        public string $type,
        public string $sectionType,
        public int $widthElement,
        public int $lengthElement,
        public int $countElement
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            type: $data['type'],
            sectionType: $data['sectionType'],
            widthElement: (int) $data['widthElement'],
            lengthElement: (int) $data['lengthElement'],
            countElement: $data['countElement']
        );
    }

    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'sectionType' => $this->sectionType,
            'widthElement' => $this->widthElement,
            'lengthElement' => $this->lengthElement,
            'countElement' => $this->countElement
        ];
    }
}
