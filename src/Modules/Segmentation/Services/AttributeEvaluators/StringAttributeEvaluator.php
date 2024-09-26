<?php

namespace Featurit\Client\Modules\Segmentation\Services\AttributeEvaluators;

use Featurit\Client\Modules\Segmentation\ConstantCollections\StringOperators;

class StringAttributeEvaluator implements AttributeEvaluator
{
    public function evaluate($value1, string $operator, $value2): bool
    {
        return match ($operator) {
            StringOperators::EQUALS             => $value1 == $value2,
            StringOperators::NOT_EQUALS         => $value1 != $value2,
            StringOperators::CONTAINS           => str_contains($value1, $value2),
            StringOperators::IS_CONTAINED_IN    => str_contains($value2, $value1),
            StringOperators::STARTS_WITH        => str_starts_with($value1, $value2),
            StringOperators::ENDS_WITH          => str_ends_with($value1, $value2),
            default                             => false,
        };
    }
}
