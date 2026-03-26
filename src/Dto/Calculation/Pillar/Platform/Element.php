<?php

declare(strict_types=1);

namespace App\Dto\Calculation\Pillar\Platform;

use App\Enum\Pillar\ElementTypeEnum;
use App\Enum\Pillar\SectionConstructTypeEnum;

class Element
{
    public function __construct(
        public ElementTypeEnum $type,
        public SectionConstructTypeEnum $sectionType,
        public int $widthElement,
        public int $lengthElement,
        public int $countElement
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            type: ElementTypeEnum::from($data['type']),
            sectionType: SectionConstructTypeEnum::from($data['sectionType']),
            widthElement: (int) $data['widthElement'],
            lengthElement: (int) $data['lengthElement'],
            countElement: $data['countElement']
        );
    }

    public function toArray(): array
    {
        return [
            'type'          => $this->type->value,
            'sectionType'   => $this->sectionType->value,
            'widthElement'  => $this->widthElement,
            'lengthElement' => $this->lengthElement,
            'countElement'  => $this->countElement
        ];
    }
}
