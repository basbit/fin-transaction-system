<?php

declare(strict_types=1);

namespace App\Finance\Infrastructure\ReadModel;

final class Sort
{
    private int $direction;

    public function __construct(
        private string $field,
        ?string $direction
    ) {
        $this->direction = match ($direction) {
            'DESC' => SORT_DESC,
            default => SORT_ASC
        };
    }

    public function getField(): string
    {
        return $this->field;
    }

    public function getDirection(): int
    {
        return $this->direction;
    }
}
