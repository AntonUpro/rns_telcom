<?php

namespace App\Enum\Pillar;

enum FormConstructEnum: string
{
    case SQUARE = 'square';
    case CIRCLE = 'circle';

    public function isCircle(): bool
    {
        return $this === self::CIRCLE;
    }
}
