<?php

namespace Featurit\Client\Tests\Modules\Analytics\Services;

use Featurit\Client\HttpClient\ClientBuilder;
use Featurit\Client\LocalCacheFactory;
use Featurit\Client\Modules\Analytics\Services\AnalyticsSender;
use Featurit\Client\Modules\Analytics\Services\FeatureAnalyticsService;
use Featurit\Client\Modules\Segmentation\DefaultFeaturitUserContext;
use Featurit\Client\Modules\Segmentation\Entities\FeatureFlag;
use Featurit\Client\Modules\Segmentation\Entities\FeatureFlagVersion;
use Http\Client\Common\Plugin\BaseUriPlugin;
use Http\Client\Common\Plugin\HeaderDefaultsPlugin;
use Http\Discovery\Psr17FactoryDiscovery;
use PHPUnit\Framework\TestCase;

class FeatureAnalyticsServiceTest extends TestCase
{
    public function test_it_can_send_a_simple_request(): void
    {
        $cacheFactory = new LocalCacheFactory();

        $clientBuilder = new ClientBuilder();
        $uriFactory = Psr17FactoryDiscovery::findUriFactory();

        $tenantIdentifier = 'test';
        $apiKey = '5b436559-e1d0-44be-96a3-65c716950c99';

        $clientBuilder->addPlugin(
            new BaseUriPlugin(
//                $uriFactory->createUri("https://{$tenantIdentifier}.featurit.com/api/v1/{$apiKey}")
                $uriFactory->createUri("http://{$tenantIdentifier}.localhost/api/v1/{$apiKey}")
            )
        );

        $clientBuilder->addPlugin(
            new HeaderDefaultsPlugin(
                [
                    'User-Agent' => 'FeaturIT',
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ]
            )
        );

        $analyticsService = new FeatureAnalyticsService(
            $cacheFactory->setLocalCache(0, "test_analytics", false),
            new AnalyticsSender($clientBuilder->getHttpClient()),
            -2
        );

        $featureFlag = new FeatureFlag(
            'TEST',
            true,
            'userId',
            [],
            [],
            null
        );

        $userContext = new DefaultFeaturitUserContext(
            '1234',
            '1357',
            '192.168.1.1',
            []
        );

        $currentTime = new \DateTime('2021-10-10 00:00:00');
        $analyticsService->registerFeatureFlagRequest(
            $featureFlag,
            $userContext,
            $currentTime
        );

        $this->assertTrue(true);
    }

    public function test_it_works_properly_with_a_minute_time_interval(): void
    {
        $cacheFactory = new LocalCacheFactory();

        $clientBuilder = new ClientBuilder();
        $uriFactory = Psr17FactoryDiscovery::findUriFactory();

        $tenantIdentifier = 'test';
        $apiKey = '5b436559-e1d0-44be-96a3-65c716950c99';

        $clientBuilder->addPlugin(
            new BaseUriPlugin(
//                $uriFactory->createUri("https://{$tenantIdentifier}.featurit.com/api/v1/{$apiKey}")
                $uriFactory->createUri("http://{$tenantIdentifier}.localhost/api/v1/{$apiKey}")
            )
        );

        $clientBuilder->addPlugin(
            new HeaderDefaultsPlugin(
                [
                    'User-Agent' => 'FeaturIT',
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ]
            )
        );

        $analyticsService = new FeatureAnalyticsService(
            $cacheFactory->setLocalCache(0, "test_analytics", false),
            new AnalyticsSender($clientBuilder->getHttpClient()),
            1
        );

        $currentTime = new \DateTime('2023-06-10 00:00:00');

        for ($i = 0; $i < 100; $i++) {
            $featureFlag = new FeatureFlag(
                'Feat',
                rand(0, 1) == 0,
                'userId',
                [],
                [],
                new FeatureFlagVersion(
                    rand(1, 0) == 0 ? 'v1' : 'v2',
                    100
                )
            );

            $userContext = new DefaultFeaturitUserContext(
                rand(1, 5) . '@gmail.com',
                '1357',
                '192.168.1.5',

                [
                    'age' => rand(25, 50),
                ]
            );

            $analyticsService->registerFeatureFlagRequest(
                $featureFlag,
                $userContext,
                $currentTime
            );

            $currentTime->add(new \DateInterval('PT1S'));

            sleep(1);
        }

        $this->assertTrue(true);
    }
}