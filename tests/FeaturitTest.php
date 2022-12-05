<?php

namespace Featurit\Client\Tests;

use ArgumentCountError;
use Exception;
use Featurit\Client\Featurit;
use Featurit\Client\HttpClient\ClientBuilder;
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
    const EXISTING_INACTIVE_FEATURE_NAME = "Login";
    const EXISTING_ACTIVE_FEATURE_NAME = "Sign Up";

    const SAMPLE_API_RESPONSE = '{
        "data": {
            "Login": false,
            "Sign Up": true,
            "Recover password": false,
            "Create User Invitation": false,
            "Create User": true,
            "Update User": false,
            "List Users": false,
            "Delete User": false,
            "View User": false
        }
    }';

    public function test_featurit_sdk_cannot_be_instantiated_without_api_key(): void
    {
        $this->expectException(ArgumentCountError::class);

        $featurit = new Featurit();
    }

    /**
     * @throws \Http\Client\Exception
     */
    public function test_featurit_throws_exception_with_invalid_api_key(): void
    {
        $this->expectException(Exception::class);

        $featurit = $this->getFeaturit(self::INVALID_API_KEY);

        $featurit->featureFlags()->all();
    }

    /**
     * @throws \Http\Client\Exception
     */
    public function test_featurit_returns_array_with_features(): void
    {
        $featurit = $this->getFeaturit(self::VALID_API_KEY);

        $featureFlags = $featurit->featureFlags()->all();

        $this->assertIsArray($featureFlags);
    }

    /**
     * @throws \Http\Client\Exception
     */
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

    /**
     * @throws \Http\Client\Exception
     */
    public function test_featurit_feature_flags_have_boolean_values(): void
    {
        $featurit = $this->getFeaturit(self::VALID_API_KEY);

        $featureFlags = $featurit->featureFlags()->all();

        $allOfTheValuesAreBooleans = true;
        foreach($featureFlags as $featureName => $isActive) {
            if (!is_bool($isActive)) {
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

    /**
     * @throws \Http\Client\Exception
     */
    public function test_is_active_shortcut_works_as_test_is_active_in_feature_flags_endpoint(): void
    {
        $featurit = $this->getFeaturit(self::VALID_API_KEY);

        $featureFlagValueShortcut = $featurit->isActive(self::EXISTING_ACTIVE_FEATURE_NAME);
        $featureFlagValueEndpoint = $featurit->featureFlags()->isActive(self::EXISTING_ACTIVE_FEATURE_NAME);

        $this->assertEquals($featureFlagValueShortcut, $featureFlagValueEndpoint);
    }

    /**
     * @param string $apiKey
     * @param int $status
     * @return Featurit
     */
    private function getFeaturit(string $apiKey, int $status = 200): Featurit
    {
        if ($apiKey !== self::VALID_API_KEY) {
            $status = 404;
        }

        $client = new Client();
        $streamFactory = new StreamFactory();
        $response = new Response($streamFactory->createStream(self::SAMPLE_API_RESPONSE), $status);
        $client->setDefaultResponse($response);
        $clientBuilder = new ClientBuilder($client);

        return new Featurit(
            self::TENANT_IDENTIFIER,
            $apiKey,
            5,
            null,
            null,
            $clientBuilder
        );
    }
}