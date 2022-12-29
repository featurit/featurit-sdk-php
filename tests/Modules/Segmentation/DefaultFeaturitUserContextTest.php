<?php

namespace Featurit\Client\Tests\Modules\Segmentation;

use Featurit\Client\Modules\Segmentation\DefaultFeaturitUserContext;
use PHPUnit\Framework\TestCase;

class DefaultFeaturitUserContextTest extends TestCase
{
    public function test_it_can_be_built_with_null_values(): void
    {
        $defaultFeaturitUserContext = new DefaultFeaturitUserContext(null, null, null);

        $this->assertInstanceOf(DefaultFeaturitUserContext::class, $defaultFeaturitUserContext);

        $this->assertEquals(null, $defaultFeaturitUserContext->getUserId());
        $this->assertEquals(null, $defaultFeaturitUserContext->getSessionId());
        $this->assertEquals(null, $defaultFeaturitUserContext->getIpAddress());
        $this->assertEquals([], $defaultFeaturitUserContext->getCustomAttributes());
    }

    public function test_it_returns_user_id(): void
    {
        $userId = "1234-2345-3456-4567";

        $defaultFeaturitUserContext = new DefaultFeaturitUserContext($userId, null, null);

        $this->assertEquals($userId, $defaultFeaturitUserContext->getUserId());
    }

    public function test_it_returns_session_id(): void
    {
        $sessionId = "abcdefgh";

        $defaultFeaturitUserContext = new DefaultFeaturitUserContext(null, $sessionId, null);

        $this->assertEquals($sessionId, $defaultFeaturitUserContext->getSessionId());
    }

    public function test_it_returns_ip_address(): void
    {
        $ipAddress = "127.0.0.1";

        $defaultFeaturitUserContext = new DefaultFeaturitUserContext(null, null, $ipAddress);

        $this->assertEquals($ipAddress, $defaultFeaturitUserContext->getIpAddress());
    }

    public function test_it_returns_custom_attribute(): void
    {
        $attributeName = "Country";
        $attributeValue = "Spain";

        $defaultFeaturitUserContext = new DefaultFeaturitUserContext(null, null, null, [
            $attributeName => $attributeValue,
        ]);

        $this->assertEquals($attributeValue, $defaultFeaturitUserContext->getCustomAttribute($attributeName));
    }

    public function test_it_verifies_custom_attribute_exists(): void
    {
        $nonExistingAttributeName = "Non Existing Attribute";
        $attributeName = "Plan";
        $attributeValue = "Free";

        $defaultFeaturitUserContext = new DefaultFeaturitUserContext(null, null, null, [
            $attributeName => $attributeValue,
        ]);

        $this->assertEquals(true, $defaultFeaturitUserContext->hasCustomAttribute($attributeName));
        $this->assertEquals(false, $defaultFeaturitUserContext->hasCustomAttribute($nonExistingAttributeName));
    }

    public function test_it_returns_all_custom_attributes(): void
    {
        $customAttributes = [
            "lang" => "ca_ES",
            "age" => 15,
            "gender" => "Female",
        ];

        $defaultFeaturitUserContext = new DefaultFeaturitUserContext(
            null,
            null,
            null,
            $customAttributes
        );

        $this->assertEquals($customAttributes, $defaultFeaturitUserContext->getCustomAttributes());
    }

    public function test_it_converts_to_array(): void
    {
        $expectedArray = [
            "userId" => 146,
            "sessionId" => null,
            "ipAddress" => "192.168.1.1",
            "gender" => "Female",
            "description" => "I like trains",
        ];

        $defaultFeaturitUserContext = new DefaultFeaturitUserContext(
            $expectedArray["userId"],
            $expectedArray["sessionId"],
            $expectedArray["ipAddress"],
            [
                "gender" => $expectedArray["gender"],
                "description" => $expectedArray["description"],
            ]
        );

        $this->assertEquals($expectedArray, $defaultFeaturitUserContext->toArray());
    }

    public function test_main_attributes_arent_overwritten_by_custom_attributes_when_it_converts_to_array(): void
    {
        $expectedArray = [
            "userId" => "totoro@gmail.com",
            "sessionId" => "a124s3243e12321",
            "ipAddress" => "192.168.1.1",
            "birth date" => "20/10/1999",
            "purchase_amount" => 979.23,
            "currency" => "EUR",
        ];

        $defaultFeaturitUserContext = new DefaultFeaturitUserContext(
            $expectedArray["userId"],
            $expectedArray["sessionId"],
            $expectedArray["ipAddress"],
            $expectedArray
        );

        $this->assertEquals($expectedArray, $defaultFeaturitUserContext->toArray());
    }

    /**
     * Test the getters for Base Attributes.
     *
     * @return void
     */
    public function test_getters_work_for_base_attributes(): void
    {
        $featuritUserContext = new DefaultFeaturitUserContext('3578', null, '33.99.11.15');

        $this->assertEquals('3578', $featuritUserContext->getUserId());
        $this->assertNull($featuritUserContext->getSessionId());
        $this->assertEquals('33.99.11.15', $featuritUserContext->getIpAddress());
    }

    /**
     * Test hasCustomAttribute.
     *
     * @return void
     */
    public function test_has_custom_attribute_method(): void
    {
        $featuritUserContext = new DefaultFeaturitUserContext('3578', null, '33.99.11.15', [
            'email' => 'info@featurit.com',
        ]);

        $this->assertTrue($featuritUserContext->hasCustomAttribute('email'));
        $this->assertFalse($featuritUserContext->hasCustomAttribute('age'));
    }

    /**
     * Test getCustomAttribute.
     *
     * @return void
     */
    public function test_get_custom_attribute_method(): void
    {
        $featuritUserContext = new DefaultFeaturitUserContext('3578', null, '33.99.11.15', [
            'email' => 'info@featurit.com',
        ]);

        $this->assertEquals('info@featurit.com', $featuritUserContext->getCustomAttribute('email'));
        $this->assertNull($featuritUserContext->getCustomAttribute('age'));
    }
}