<?php

namespace Featurit\Client\Tests\Modules\Analytics\Services;

use DateInterval;
use DateTime;
use Featurit\Client\LocalCacheFactory;
use Featurit\Client\Modules\Analytics\Services\AnalyticsSender;
use Featurit\Client\Modules\Analytics\Services\FeatureAnalyticsService;
use Featurit\Client\Modules\Segmentation\ConstantCollections\BaseAttributes;
use Featurit\Client\Modules\Segmentation\Entities\FeatureFlag;
use Featurit\Client\Modules\Segmentation\Entities\FeatureFlagVersion;
use PHPUnit\Framework\TestCase;

class FeatureAnalyticsServiceTest extends TestCase
{
    const TEST_CACHE_DIR = "analytics_test";
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

    public function test_it_can_send_a_simple_request_with_negative_analytics_interval(): void
    {
        $cacheFactory = new LocalCacheFactory();

        $mockAnalyticsSender = $this->createMock(AnalyticsSender::class);

        $analyticsService = new FeatureAnalyticsService(
            $cacheFactory->setLocalCache(0, self::TEST_CACHE_DIR, false),
            $mockAnalyticsSender,
            -2
        );

        $featureFlag = new FeatureFlag(
            'TEST',
            true,
            BaseAttributes::USER_ID,
            [],
            [],
            null
        );

        $currentTime = new DateTime('2021-10-10 00:00:00');
        $analyticsService->registerFeatureFlagRequest(
            $featureFlag,
            $currentTime
        );

        $this->assertTrue(true);
    }

    public function test_it_works_properly_with_60_seconds_time_interval(): void
    {
        $cacheFactory = new LocalCacheFactory();

        $mockAnalyticsSender = $this->createMock(AnalyticsSender::class);

        $analyticsService = new FeatureAnalyticsService(
            $cacheFactory->setLocalCache(0, self::TEST_CACHE_DIR, false),
            $mockAnalyticsSender,
            1
        );

        $currentTime = new DateTime('2023-06-10 00:00:00');

        for ($i = 0; $i < 60; $i++) {
            $featureFlag = new FeatureFlag(
                'Feat',
                rand(0, 1) == 0,
                BaseAttributes::USER_ID,
                [],
                [],
                new FeatureFlagVersion(
                    rand(0, 1) == 0 ? 'v1' : 'v2',
                    100
                )
            );

            $analyticsService->registerFeatureFlagRequest(
                $featureFlag,
                $currentTime
            );

            $currentTime->add(new DateInterval('PT1S'));

            sleep(1);
        }

        $this->assertTrue(true);
    }

//    public function test_it_works_properly_indefinitely(): void
//    {
//        $cacheFactory = new LocalCacheFactory();
//
//        $clientBuilder = new ClientBuilder();
//        $uriFactory = Psr17FactoryDiscovery::findUriFactory();
//
//        $tenantIdentifier = 'test';
//        $apiKey = '5b436559-e1d0-44be-96a3-65c716950c99';
//
//        $clientBuilder->addPlugin(
//            new BaseUriPlugin(
////                $uriFactory->createUri("https://{$tenantIdentifier}.featurit.com/api/v1/{$apiKey}")
//                $uriFactory->createUri("http://{$tenantIdentifier}.localhost/api/v1/{$apiKey}")
//            )
//        );
//
//        $clientBuilder->addPlugin(
//            new HeaderDefaultsPlugin(
//                [
//                    'User-Agent' => 'FeaturIT',
//                    'Content-Type' => 'application/json',
//                    'Accept' => 'application/json',
//                ]
//            )
//        );
//
//        $analyticsService = new FeatureAnalyticsService(
//            $cacheFactory->setLocalCache(0, self::TEST_CACHE_DIR, false),
//            new AnalyticsSender($clientBuilder->getHttpClient()),
//            1
//        );
//
//        while (true) {
//            $featureFlag = new FeatureFlag(
//                'Feat' . rand(0, 100),
//                rand(0, 1) == 0,
//                BaseAttributes::USER_ID,
//                [],
//                [],
//                new FeatureFlagVersion(
//                    'v' . rand(0, 5),
//                    100
//                )
//            );
//
//            $analyticsService->registerFeatureFlagRequest(
//                $featureFlag
//            );
//
//            usleep(10);
//        }
//
//        $this->assertTrue(true);
//    }
}