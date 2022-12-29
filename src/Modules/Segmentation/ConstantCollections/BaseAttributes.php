<?php

namespace Featurit\Client\Modules\Segmentation\ConstantCollections;

class BaseAttributes implements ConstantCollection
{
    const USER_ID = 'userId';
    const SESSION_ID = 'sessionId';
    const IP_ADDRESS = 'ipAddress';

    public static function all(): array
    {
        return [
            self::USER_ID,
            self::SESSION_ID,
            self::IP_ADDRESS,
        ];
    }
}
