<?php

declare(strict_types=1);

namespace App\Service\Calculation\Pillar;

use App\Dto\Calculation\Pillar\Calculate\SectionDto;
use App\Dto\Calculation\Pillar\Calculate\StrengtheningDto;
use App\Enum\Pillar\FormConstructEnum;
use App\Enum\Pillar\PillarEnum;

class SectionBuilderService
{
    private const HEIGHT_OF_SECTION = 5000;

    /**
     * @return SectionDto[]
     */
    public function build(PillarEnum $pillarEnum, float $height, float $markBottom, ?StrengtheningDto $strengtheningDto): array
    {
        $sections = [];
        $heightStrengthening = $strengtheningDto?->height ?? 0;
        $countSectionsInPillar = (int)ceil(($height - $heightStrengthening) / self::HEIGHT_OF_SECTION);

        $numberSection = 1;
        for ($i = 0; $i < $countSectionsInPillar; $i++) {
            $heightSection = $i === $countSectionsInPillar - 1
                ? $height - ($countSectionsInPillar - 1) * self::HEIGHT_OF_SECTION - $heightStrengthening
                : self::HEIGHT_OF_SECTION;
            $sections[] = new SectionDto(
                number: $numberSection,
                height: $heightSection,
                diameterTop: $pillarEnum->getDiameterAtHeight($pillarEnum->getHeight() - self::HEIGHT_OF_SECTION * $i),
                diameterBottom: $pillarEnum->getDiameterAtHeight($pillarEnum->getHeight() - self::HEIGHT_OF_SECTION * $i - $heightSection),
                topMark: $markBottom + $height - self::HEIGHT_OF_SECTION * $i,
                formConstruct: FormConstructEnum::CIRCLE,
            );
            $numberSection++;
        }

        if ($strengtheningDto) {
            $countSectionsInStrengthening = (int)ceil($heightStrengthening / self::HEIGHT_OF_SECTION);
            for ($i = 0; $i < $countSectionsInStrengthening; $i++) {
                $heightSection = $i === $countSectionsInStrengthening - 1
                    ? $heightStrengthening - ($countSectionsInStrengthening - 1) * self::HEIGHT_OF_SECTION
                    : self::HEIGHT_OF_SECTION;
                $sections[] = new SectionDto(
                    number: $numberSection,
                    height: $heightSection,
                    diameterTop: $strengtheningDto->width,
                    diameterBottom: $strengtheningDto->width,
                    topMark: $markBottom + $height - self::HEIGHT_OF_SECTION * $i,
                    formConstruct: $strengtheningDto->type,
                );
            }
        }

        return $sections;
    }
}
