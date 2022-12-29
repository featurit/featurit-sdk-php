<?php

namespace Featurit\Client\Modules\Segmentation\ConstantCollections;

class StringOperators implements ConstantCollection
{
    const EQUALS = 'EQUALS';
    const NOT_EQUALS = 'NOT_EQUALS';
    const CONTAINS = 'CONTAINS';
    const STARTS_WITH = 'STARTS_WITH';
    const ENDS_WITH = 'ENDS_WITH';

    public static function all(): array
    {
        return [
            self::EQUALS,
            self::NOT_EQUALS,
            self::CONTAINS,
            self::STARTS_WITH,
            self::ENDS_WITH,
        ];
    }
}
