<?php

namespace Featurit\Client\Tests;

use Exception;
use Featurit\Client\Featurit;
use Featurit\Client\FeaturitBuilder;
use Featurit\Client\HttpClient\ClientBuilder;
use Featurit\Client\Modules\Segmentation\DefaultFeaturitUserContext;
use Featurit\Client\Modules\Segmentation\DefaultFeaturitUserContextProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Psr16Cache;

class FeaturitBuilderTest extends TestCase
{
    const TENANT_IDENTIFIER = "tenant-name";

    const INVALID_API_KEY = "f48c1378-24dc-4d04-8208-acef34d51dae";
    const VALID_API_KEY = "e39e2919-13ca-4a14-1739-ecdf32d51dba";

    const CACHE_TTL_MINUTES = 1;
    const ANALYTICS_INTERVAL_MINUTES = 5;

    /**
     * @throws Exception
     */
    public function test_featurit_builder_returns_an_instance_of_featurit_with_tenant_id_and_valid_api_key_only(): void
    {
        $featurit = (new FeaturitBuilder())
            ->setTenantIdentifier(self::TENANT_IDENTIFIER)
            ->setApiKey(self::VALID_API_KEY)
            ->build();

        $this->assertInstanceOf(Featurit::class, $featurit);
    }

    /**
     * @throws Exception
     */
    public function test_featurit_builder_returns_an_instance_of_featurit_with_all_params(): void
    {
        $featurit = (new FeaturitBuilder())
            ->setTenantIdentifier(self::TENANT_IDENTIFIER)
            ->setApiKey(self::VALID_API_KEY)
            ->setCacheTtlMinutes(self::CACHE_TTL_MINUTES)
            ->setFeaturitUserContextProvider(new DefaultFeaturitUserContextProvider())
            ->setCache(new Psr16Cache(new FilesystemAdapter()))
            ->setHttpClientBuilder(new ClientBuilder())
            ->setUserContext(new DefaultFeaturitUserContext(null, null, null))
            ->setIsAnalyticsModuleEnabled(true)
            ->setSendAnalyticsIntervalMinutes(self::ANALYTICS_INTERVAL_MINUTES)
            ->setIsTrackingModuleEnabled(true)
            ->build();

        $this->assertInstanceOf(Featurit::class, $featurit);
    }

    /**
     * @throws Exception
     */
    public function test_featurit_builder_returns_an_instance_of_featurit_with_tenant_id_and_invalid_api_key(): void
    {
        $featurit = (new FeaturitBuilder())
            ->setTenantIdentifier(self::TENANT_IDENTIFIER)
            ->setApiKey(self::INVALID_API_KEY)
            ->build();

        $this->assertInstanceOf(Featurit::class, $featurit);
    }

    public function test_featurit_builder_fails_when_no_api_key_is_given(): void
    {
        $this->expectException(Exception::class);

        $featurit = (new FeaturitBuilder())
            ->setTenantIdentifier(self::TENANT_IDENTIFIER)
            ->build();
    }
}