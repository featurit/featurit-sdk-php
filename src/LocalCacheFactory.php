<?php

namespace Featurit\Client;

use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Psr16Cache;

class LocalCacheFactory
{
    /**
     * @param int $cacheTtlMinutes
     * @param string $path
     * @param bool $expires
     * @return CacheInterface
     */
    public function setLocalCache(int $cacheTtlMinutes, string $path = 'cache', bool $expires = true): CacheInterface
    {
        $cachePath = join(DIRECTORY_SEPARATOR, [dirname(__FILE__), '..', $path]);

        $cacheFinalTtl = 0;

        if ($expires) {
            $cacheFinalTtl = 60 * $cacheTtlMinutes;
        }

        return new Psr16Cache(new FilesystemAdapter("", $cacheFinalTtl, $cachePath));
    }
}