<?php

namespace Featurit\Client\Modules\Segmentation\Entities;

class NumberSegmentRule
{
    public function __construct(
        private string $attribute,
        private string $operator,
        private int $value
    ) {}

    public function attribute(): string
    {
        return $this->attribute;
    }

    public function operator(): string
    {
        return $this->operator;
    }

    public function value(): int
    {
        return $this->value;
    }

    public function toArray(): array
    {
        return [
            'attribute' => $this->attribute(),
            'operator' => $this->operator(),
            'value' => $this->value(),
        ];
    }
}
