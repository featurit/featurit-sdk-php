<?php

namespace Featurit\Client\Modules\Segmentation\ConstantCollections;

class AttributeTypes implements ConstantCollection
{
    const STRING = 'STRING';
    const NUMBER = 'NUMBER';

    public static function all(): array
    {
        return [
            self::STRING,
            self::NUMBER,
        ];
    }
}
