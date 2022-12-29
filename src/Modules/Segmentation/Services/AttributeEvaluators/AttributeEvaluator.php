<?php

namespace Featurit\Client\Modules\Segmentation\Services\AttributeEvaluators;

interface AttributeEvaluator
{
    public function evaluate($value1, string $operator,$value2): bool;
}
