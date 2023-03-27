<?php

namespace Featurit\Client\Tests;

use ArgumentCountError;
use Featurit\Client\Featurit;
use Featurit\Client\FeaturitBuilder;
use Featurit\Client\HttpClient\ClientBuilder;
use Featurit\Client\HttpClient\Exceptions\InvalidApiKeyException;
use Featurit\Client\Modules\Analytics\AnalyticsBucket;
use Featurit\Client\Modules\Segmentation\ConstantCollections\BaseVersions;
use Featurit\Client\Modules\Segmentation\DefaultFeaturitUserContext;
use Featurit\Client\Modules\Segmentation\DefaultFeaturitUserContextProvider;
use Featurit\Client\Modules\Segmentation\FeaturitUserContext;
use Featurit\Client\Modules\Segmentation\FeaturitUserContextProvider;
use Http\Mock\Client;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\StreamFactory;
use PHPUnit\Framework\TestCase;

class FeaturitTest extends TestCase
{
    const TENANT_IDENTIFIER = "tenant-name";

    const INVALID_API_KEY = "f48c1378-24dc-4d04-8208-acef34d51dae";
    const VALID_API_KEY = "e39e2919-13ca-4a14-1739-ecdf32d51dba";

    const NON_EXISTING_FEATURE_NAME = "NON_EXISTING_FEATURE_NAME";
    const EXISTING_INACTIVE_FEATURE_NAME = "Feat2";
    const EXISTING_ACTIVE_FEATURE_NAME = "SimpleFeat";

    const SAMPLE_API_RESPONSE = '{
        "data": {
            "SimpleFeat": {
                "name": "SimpleFeat",
                "active": true,
                "distribution_attribute": "userId",
                "segments": [],
                "versions": []
            },
            "Feat": {
                "name": "Feat",
                "active": true,
                "distribution_attribute": "userId",
                "segments": [
                    {
                        "rollout_attribute": "ipAddress",
                        "rollout_percentage": 100,
                        "string_rules": [
                            {
                                "attribute": "userId",
                                "operator": "EQUALS",
                                "value": "1"
                            }
                        ],
                        "number_rules": []
                    }
                ],
                "versions": [
                    {
                        "name": "v1",
                        "distribution_percentage": 45
                    },
                    {
                        "name": "v2",
                        "distribution_percentage": 55
                    }
                ]
            },
            "Feat2": {
                "name": "Feat2",
                "active": false,
                "distribution_attribute": "userId",
                "segments": [
                    {
                        "rollout_attribute": "ipAddress",
                        "rollout_percentage": 100,
                        "string_rules": [
                            {
                                "attribute": "userId",
                                "operator": "EQUALS",
                                "value": "1"
                            }
                        ],
                        "number_rules": []
                    }
                ],
                "versions": [
                    {
                        "name": "v1",
                        "distribution_percentage": 45
                    },
                    {
                        "name": "v2",
                        "distribution_percentage": 55
                    }
                ]
            }
        }
    }';

    public function test_featurit_sdk_cannot_be_instantiated_without_api_key(): void
    {
        $this->expectException(ArgumentCountError::class);

        $featurit = new Featurit();
    }

    public function test_featurit_throws_exception_with_invalid_api_key(): void
    {
        $this->expectException(InvalidApiKeyException::class);

        $featurit = $this->getFeaturit(self::INVALID_API_KEY);

        $featurit->featureFlags()->all();
    }

    public function test_featurit_returns_array_with_features(): void
    {
        $featurit = $this->getFeaturit(self::VALID_API_KEY);

        $featureFlags = $featurit->featureFlags()->all();

        $this->assertIsArray($featureFlags);
    }

    public function test_featurit_feature_flags_have_string_keys(): void
    {
        $featurit = $this->getFeaturit(self::VALID_API_KEY);

        $featureFlags = $featurit->featureFlags()->all();

        $allOfTheValuesAreStrings = true;
        foreach($featureFlags as $featureName => $isActive) {
            if (!is_string($featureName)) {
                $allOfTheValuesAreStrings = false;
                break;
            }
        }

        $this->assertTrue($allOfTheValuesAreStrings);
    }

    public function test_featurit_feature_flags_have_boolean_values(): void
    {
        $featurit = $this->getFeaturit(self::VALID_API_KEY);

        $featureFlags = $featurit->featureFlags()->all();
        $allOfTheValuesAreBooleans = true;
        foreach($featureFlags as $featureName => $featureFlag) {
            if (!is_bool($featureFlag->isActive())) {
                $allOfTheValuesAreBooleans = false;
                break;
            }
        }

        $this->assertTrue($allOfTheValuesAreBooleans);
    }

    public function test_is_active_returns_false_if_feature_name_doesnt_exist(): void
    {
        $featurit = $this->getFeaturit(self::VALID_API_KEY);

        $featureFlagValue = $featurit->featureFlags()->isActive(self::NON_EXISTING_FEATURE_NAME);

        $this->assertFalse($featureFlagValue);
    }

    public function test_is_active_returns_false_if_feature_name_exists_but_is_not_active(): void
    {
        $featurit = $this->getFeaturit(self::VALID_API_KEY);

        $featureFlagValue = $featurit->featureFlags()->isActive(self::EXISTING_INACTIVE_FEATURE_NAME);

        $this->assertFalse($featureFlagValue);
    }

    public function test_is_active_returns_true_if_feature_name_exists_and_is_active(): void
    {
        $featurit = $this->getFeaturit(self::VALID_API_KEY);

        $featureFlagValue = $featurit->featureFlags()->isActive(self::EXISTING_ACTIVE_FEATURE_NAME);

        $this->assertTrue($featureFlagValue);
    }

    public function test_is_active_shortcut_works_as_is_active_in_feature_flags_endpoint(): void
    {
        $featurit = $this->getFeaturit(self::VALID_API_KEY);

        $featureFlagValueShortcut = $featurit->isActive(self::EXISTING_ACTIVE_FEATURE_NAME);
        $featureFlagValueEndpoint = $featurit->featureFlags()->isActive(self::EXISTING_ACTIVE_FEATURE_NAME);

        $this->assertEquals($featureFlagValueShortcut, $featureFlagValueEndpoint);
    }

    public function test_version_returns_false_if_feature_doesnt_exist(): void
    {
        $featurit = $this->getFeaturit(self::VALID_API_KEY);

        $featureFlagVersion = $featurit->featureFlags()->version(self::NON_EXISTING_FEATURE_NAME);

        $this->assertEquals(BaseVersions::DEFAULT, $featureFlagVersion);
    }

    public function test_version_returns_default_if_feature_doesnt_have_versions(): void
    {
        $featurit = $this->getFeaturit(self::VALID_API_KEY);

        $featureFlagVersion = $featurit->featureFlags()->version(self::EXISTING_ACTIVE_FEATURE_NAME);

        $this->assertEquals(BaseVersions::DEFAULT, $featureFlagVersion);
    }

    public function test_version_returns_properly_if_feature_has_versions_and_no_context_is_passed(): void
    {
        $featurit = $this->getFeaturit(self::VALID_API_KEY);

        $featureFlagVersion = $featurit->featureFlags()->version(self::EXISTING_INACTIVE_FEATURE_NAME);

        $this->assertEquals('v1', $featureFlagVersion);
    }

    public function test_version_returns_properly_if_feature_has_versions_and_some_context_is_passed(): void
    {
        $featuritUserContextProvider = new DefaultFeaturitUserContextProvider(
            new DefaultFeaturitUserContext('1235', null, null)
        );

        $featurit = $this->getFeaturit(self::VALID_API_KEY, 200, $featuritUserContextProvider);

        $featureFlagVersion = $featurit->featureFlags()->version(self::EXISTING_INACTIVE_FEATURE_NAME);

        $this->assertEquals('v1', $featureFlagVersion);

        $featuritUserContextProvider = new DefaultFeaturitUserContextProvider(
            new DefaultFeaturitUserContext('1234', null, null)
        );

        $featurit = $this->getFeaturit(self::VALID_API_KEY, 200, $featuritUserContextProvider);

        $featureFlagVersion = $featurit->featureFlags()->version(self::EXISTING_INACTIVE_FEATURE_NAME);

        $this->assertEquals('v2', $featureFlagVersion);
    }

    public function test_version_shortcut_works_as_version_in_feature_flags_endpoint(): void
    {
        $featurit = $this->getFeaturit(self::VALID_API_KEY);

        $featureFlagValueShortcut = $featurit->version(self::EXISTING_INACTIVE_FEATURE_NAME);
        $featureFlagValueEndpoint = $featurit->featureFlags()->version(self::EXISTING_INACTIVE_FEATURE_NAME);

        $this->assertEquals($featureFlagValueShortcut, $featureFlagValueEndpoint);
    }

    public function test_passing_user_context_in_constructor_overrides_user_context_provider(): void
    {
        $featuritUserContextProvider = new DefaultFeaturitUserContextProvider(
            new DefaultFeaturitUserContext("1234", "12ab3cf", "127.0.0.1")
        );

        $featurit = $this->getFeaturit(
            self::VALID_API_KEY,
            200,
            $featuritUserContextProvider,
            new DefaultFeaturitUserContext("1357", null, null)
        );

        $this->assertEquals("1357", $featurit->getUserContext()->getUserId());
    }

    public function test_passing_user_context_in_setter_overrides_user_context_provider(): void
    {
        $featuritUserContextProvider = new DefaultFeaturitUserContextProvider(
            new DefaultFeaturitUserContext("1234", "12ab3cf", "127.0.0.1")
        );

        $featurit = $this->getFeaturit(self::VALID_API_KEY, 200, $featuritUserContextProvider);

        $this->assertEquals("1234", $featurit->getUserContext()->getUserId());

        $featurit->setUserContext(new DefaultFeaturitUserContext("1357", null, null));

        $this->assertEquals("1357", $featurit->getUserContext()->getUserId());
    }

    public function test_that_cache_stores_dates_properly(): void
    {
        $featurit = $this->getFeaturit(self::VALID_API_KEY);

        $date = new \DateTime("2020-02-11");

        $featurit->getCache()->set("test_datetime_serdes", $date);

        $cachedDate = $featurit->getCache()->get("test_datetime_serdes");

        $this->assertEquals($date, $cachedDate);

        $date = new \DateTime('now');

        $featurit->getCache()->set("test_datetime_serdes", $date);

        $cachedDate = $featurit->getCache()->get("test_datetime_serdes");

        $this->assertEquals($date, $cachedDate);

        $featurit->getCache()->delete("test_datetime_serdes");
    }

    public function test_that_cache_stores_analytics_bucket_properly(): void
    {
        $featurit = $this->getFeaturit(self::VALID_API_KEY);

        $date = new \DateTime("2020-02-11");
        $analyticsBucket = new AnalyticsBucket($date);

        $featurit->getBackupCache()->set("test_analytics_bucket_serdes", $analyticsBucket);

        $cachedAnalyticsBucket = $featurit->getBackupCache()->get("test_analytics_bucket_serdes");

        $this->assertEquals($analyticsBucket, $cachedAnalyticsBucket);

        $featurit->getBackupCache()->delete("test_analytics_bucket_serdes");
    }

    /**
     * @param string $apiKey
     * @param int $status
     * @param FeaturitUserContextProvider|null $featuritUserContextProvider
     * @param FeaturitUserContext|null $featuritUserContext
     * @return Featurit
     * @throws \Exception
     */
    private function getFeaturit(
        string $apiKey,
        int $status = 200,
        FeaturitUserContextProvider $featuritUserContextProvider = null,
        FeaturitUserContext $featuritUserContext = null,
    ): Featurit
    {
        if ($apiKey !== self::VALID_API_KEY) {
            $status = 404;
        }

        $client = new Client();
        $streamFactory = new StreamFactory();
        $response = new Response($streamFactory->createStream(self::SAMPLE_API_RESPONSE), $status);
        $client->setDefaultResponse($response);
        $clientBuilder = new ClientBuilder($client);

        $featuritBuilder = (new FeaturitBuilder())
            ->setTenantIdentifier(self::TENANT_IDENTIFIER)
            ->setApiKey($apiKey)
            ->setCacheTtlMinutes(5)
            ->setSendAnalyticsIntervalMinutes(1)
            ->setHttpClientBuilder($clientBuilder);

        if (! is_null($featuritUserContextProvider)) {
            $featuritBuilder->setFeaturitUserContextProvider($featuritUserContextProvider);
        }

        if (! is_null($featuritUserContext)) {
            $featuritBuilder->setUserContext($featuritUserContext);
        }

        return $featuritBuilder->build();
    }
}