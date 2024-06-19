<?php

namespace Featurit\Client\Tests;

use Featurit\Client\LocalCacheFactory;
use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\CacheInterface;

class LocalCacheFactoryTest extends TestCase
{
    const TEST_CACHE_DIR = "cache_test";
    private $testCacheDir = "";

    protected function setUp(): void
    {
        $this->testCacheDir = join(DIRECTORY_SEPARATOR, [dirname(__FILE__), '..', self::TEST_CACHE_DIR]);
    }

    protected function tearDown(): void
    {
        $this->deleteDirectory($this->testCacheDir);
    }

    private function deleteDirectory($dir): bool
    {
        if (!file_exists($dir)) {
            return true;
        }

        if (!is_dir($dir)) {
            return unlink($dir);
        }

        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }

            if (!$this->deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
                return false;
            }
        }

        return rmdir($dir);
    }

    public function test_local_cache_factory_returns_a_cache_interface_instance(): void
    {
        $localCacheFactory = new LocalCacheFactory();

        $localCache = $localCacheFactory->setLocalCache(1, self::TEST_CACHE_DIR);

        $this->assertInstanceOf(CacheInterface::class, $localCache);
    }

    public function test_returned_cache_works_properly(): void
    {
        $localCacheFactory = new LocalCacheFactory();

        $localCache = $localCacheFactory->setLocalCache(5, self::TEST_CACHE_DIR);

        $localCache->set('test_key', 'test_value');

        $value = $localCache->get('test_key');

        $this->assertEquals('test_value', $value);
    }

    public function test_returned_cache_expires_after_1_minute(): void
    {
        $localCacheFactory = new LocalCacheFactory();

        $localCache = $localCacheFactory->setLocalCache(1, self::TEST_CACHE_DIR);

        $localCache->set('test_key_2', 'test_value');

        $localCache = $localCacheFactory->setLocalCache(1, self::TEST_CACHE_DIR);

        $value = $localCache->get('test_key_2');

        $this->assertEquals('test_value', $value);

        sleep(1 * 60);

        $localCache = $localCacheFactory->setLocalCache(1, self::TEST_CACHE_DIR);

        $value = $localCache->get('test_key_2');

        $this->assertNull($value);
    }
}