<?php

namespace Featurit\Client;

use Laminas\Cache\Psr\SimpleCache\SimpleCacheDecorator;
use Laminas\Cache\Storage\Adapter\Filesystem;
use Laminas\Cache\Storage\Plugin\ExceptionHandler;
use Laminas\Cache\Storage\Plugin\Serializer;
use Psr\SimpleCache\CacheInterface;

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
        $storage = new Filesystem();

        $plugin = new ExceptionHandler();
        $plugin->getOptions()->setThrowExceptions(true);

        $storage->addPlugin($plugin);

        $plugin = new Serializer();
        $storage->addPlugin($plugin);

        $storage->getOptions()->setTtl($cacheTtlMinutes * 60);

        $cacheDirName = join(DIRECTORY_SEPARATOR, [dirname(__FILE__), '..', $path]);

        if (!file_exists($cacheDirName)) {
            mkdir($cacheDirName, 0775, true);
        }

        $storage->getOptions()->setCacheDir($cacheDirName);

        if ($expires) {
            $storage->clearExpired();
        }

        return new SimpleCacheDecorator($storage);
    }
}