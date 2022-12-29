<?php

namespace Featurit\Client\Modules\Segmentation\Services\AttributeEvaluators;

use Featurit\Client\Modules\Segmentation\ConstantCollections\NumberOperators;

class NumberAttributeEvaluator implements AttributeEvaluator
{
    public function evaluate($value1, string $operator, $value2): bool
    {
        return match ($operator) {
            NumberOperators::LESS_THAN          => $value1 < $value2,
            NumberOperators::LESS_EQUAL_THAN    => $value1 <= $value2,
            NumberOperators::EQUAL              => $value1 == $value2,
            NumberOperators::NOT_EQUAL          => $value1 != $value2,
            NumberOperators::GREATER_EQUAL_THAN => $value1 >= $value2,
            NumberOperators::GREATER_THAN       => $value1 > $value2,
            default => false,
        };
    }
}
