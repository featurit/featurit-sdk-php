<?php

namespace Featurit\Client\Modules\Segmentation\ConstantCollections;

class BaseVersions implements ConstantCollection
{
    const DEFAULT = 'default';

    public static function all(): array
    {
        return [
            self::DEFAULT,
        ];
    }
}
