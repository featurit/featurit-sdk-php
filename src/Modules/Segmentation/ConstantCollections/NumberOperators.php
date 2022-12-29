<?php

namespace Featurit\Client\Modules\Segmentation\ConstantCollections;

class NumberOperators implements ConstantCollection
{
    const LESS_THAN = 'LESS_THAN';
    const LESS_EQUAL_THAN = 'LESS_EQUAL_THAN';
    const EQUAL = 'EQUAL';
    const NOT_EQUAL = 'NOT_EQUAL';
    const GREATER_EQUAL_THAN = 'GREATER_EQUAL_THAN';
    const GREATER_THAN = 'GREATER_THAN';

    public static function all(): array
    {
        return [
            self::LESS_THAN,
            self::LESS_EQUAL_THAN,
            self::EQUAL,
            self::NOT_EQUAL,
            self::GREATER_EQUAL_THAN,
            self::GREATER_THAN,
        ];
    }
}
