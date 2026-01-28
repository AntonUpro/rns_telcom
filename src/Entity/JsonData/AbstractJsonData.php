<?php

declare(strict_types=1);

namespace App\Entity\JsonData;

abstract class AbstractJsonData
{
    public abstract function toArray(): array;

    public static abstract function fromArray(array $data): static;
}
