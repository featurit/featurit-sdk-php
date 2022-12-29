<?php

namespace Featurit\Client\Modules\Segmentation\Entities;

class StringSegmentRule
{
    public function __construct(
        private string $attribute,
        private string $operator,
        private string $value
    ) {}

    public function attribute(): string
    {
        return $this->attribute;
    }

    public function operator(): string
    {
        return $this->operator;
    }

    public function value(): string
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
