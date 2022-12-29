<?php

namespace Featurit\Client\Tests\Modules\Segmentation\Services;

use Featurit\Client\Modules\Segmentation\ConstantCollections\BaseAttributes;
use Featurit\Client\Modules\Segmentation\ConstantCollections\StringOperators;
use Featurit\Client\Modules\Segmentation\DefaultFeaturitUserContext as FeaturitUserContext;
use Featurit\Client\Modules\Segmentation\Entities\FeatureFlag;
use Featurit\Client\Modules\Segmentation\Entities\FeatureFlagSegment;
use Featurit\Client\Modules\Segmentation\Entities\StringSegmentRule;
use Featurit\Client\Modules\Segmentation\Services\BucketDistributors\BucketDistributor;
use Featurit\Client\Modules\Segmentation\Services\FeatureSegmentationService;
use PHPUnit\Framework\TestCase;

class FeatureSegmentationServiceTest extends TestCase
{
    protected array $baseAttributes;

    /**
     * Test the no features case.
     *
     * @return void
     */
    public function test_the_service_returns_an_empty_feature_array_when_there_are_no_flags(): void
    {
        $featureSegmentationService = new FeatureSegmentationService();

        $featuritUserContext = new FeaturitUserContext(null, null, null);
        $featureFlags = [];

        $segmentedFeatureFlags = $featureSegmentationService->execute(
            $featureFlags,
            $featuritUserContext
        );

        $this->assertEmpty($segmentedFeatureFlags);
    }

    /**
     * Test the no segments case.
     *
     * @return void
     */
    public function test_the_service_returns_the_same_features_when_no_segmentation_is_applied(): void
    {
        $featureFlags = $this->seedOneSimpleFeatureFlag();

        $this->assertCount(1, $featureFlags);
        $this->assertFalse(current($featureFlags)->isActive());

        $featureSegmentationService = new FeatureSegmentationService();

        $featuritUserContext = new FeaturitUserContext(null, null, null);

        $segmentedFeatureFlags = $featureSegmentationService->execute(
            $featureFlags,
            $featuritUserContext
        );

        $this->assertEquals($featureFlags, $segmentedFeatureFlags);
    }

    /**
     * Test the no segment matches case.
     *
     * @return void
     */
    public function test_the_service_returns_the_feature_disabled_when_no_segment_matches(): void
    {
        $featureFlags = $this->seedOneActiveFeatureFlagWithASegmentWithOneUserIdRule();

        $this->assertCount(1, $featureFlags);
        $this->assertTrue(current($featureFlags)->isActive());

        $featureSegmentationService = new FeatureSegmentationService();

        $featuritUserContext = new FeaturitUserContext('1111', null, null);

        $segmentedFeatureFlags = $featureSegmentationService->execute(
            $featureFlags,
            $featuritUserContext
        );

        $this->assertFalse(current($segmentedFeatureFlags)->isActive());
    }

    /**
     * Test the custom attributes case.
     *
     * @return void
     */
    public function test_custom_attributes_dont_match(): void
    {
        $featureFlags = $this->seedOneActiveFeatureFlagWithASegmentWithOneCustomAttributeRule();

        $this->assertCount(1, $featureFlags);
        $this->assertTrue(current($featureFlags)->isActive());

        $featureSegmentationService = new FeatureSegmentationService();

        $featuritUserContext = new FeaturitUserContext(null, null, null, [
            'email' => 'featurit.tech@gmail.com',
        ]);

        $segmentedFeatureFlags = $featureSegmentationService->execute(
            $featureFlags,
            $featuritUserContext
        );

        $this->assertFalse(current($segmentedFeatureFlags)->isActive());
    }

    /**
     * Test the custom attributes case.
     *
     * @return void
     */
    public function test_custom_attributes_match(): void
    {
        $featureFlags = $this->seedOneActiveFeatureFlagWithASegmentWithOneCustomAttributeRule();

        $this->assertCount(1, $featureFlags);
        $this->assertTrue(current($featureFlags)->isActive());

        $featureSegmentationService = new FeatureSegmentationService();

        $featuritUserContext = new FeaturitUserContext(null, null, null, [
            'email' => 'info@featurit.com',
        ]);

        $segmentedFeatureFlags = $featureSegmentationService->execute(
            $featureFlags,
            $featuritUserContext
        );

        $this->assertTrue(current($segmentedFeatureFlags)->isActive());
    }

    /**
     * Test the custom attributes case.
     *
     * @return void
     */
    public function test_custom_attributes_arent_present(): void
    {
        $featureFlags = $this->seedOneActiveFeatureFlagWithASegmentWithOneCustomAttributeRule();

        $this->assertCount(1, $featureFlags);
        $this->assertTrue(current($featureFlags)->isActive());

        $featureSegmentationService = new FeatureSegmentationService();

        $featuritUserContext = new FeaturitUserContext(null, null, null);

        $segmentedFeatureFlags = $featureSegmentationService->execute(
            $featureFlags,
            $featuritUserContext
        );

        $this->assertFalse(current($segmentedFeatureFlags)->isActive());
    }

    /**
     * Test the no segment matches case.
     *
     * @return void
     */
    public function test_it_returns_the_feature_enabled_when_the_segment_matches(): void
    {
        $featureFlags = $this->seedOneActiveFeatureFlagWithASegmentWithOneUserIdRule();

        $this->assertCount(1, $featureFlags);
        $this->assertTrue(current($featureFlags)->isActive());

        $featureSegmentationService = new FeatureSegmentationService();

        $featuritUserContext = new FeaturitUserContext('12345', null, null);

        $segmentedFeatureFlags = $featureSegmentationService->execute(
            $featureFlags,
            $featuritUserContext
        );

        $this->assertTrue(current($segmentedFeatureFlags)->isActive());
    }

    /**
     * Test the one of the two segment rules matches.
     *
     * @return void
     */
    public function test_it_returns_the_feature_disabled_when_only_one_of_two_segment_rules_matches(): void
    {
        $featureFlags = $this->seedOneActiveFeatureFlagWithASegmentWithTwoRules();

        $this->assertCount(1, $featureFlags);
        $this->assertTrue(current($featureFlags)->isActive());

        $featureSegmentationService = new FeatureSegmentationService();

        $featuritUserContext = new FeaturitUserContext('12345', null, null);

        $segmentedFeatureFlags = $featureSegmentationService->execute(
            $featureFlags,
            $featuritUserContext
        );

        $this->assertFalse(current($segmentedFeatureFlags)->isActive());

        $featuritUserContext = new FeaturitUserContext(null, null, '127.0.0.1');

        $segmentedFeatureFlags = $featureSegmentationService->execute(
            $featureFlags,
            $featuritUserContext
        );

        $this->assertFalse(current($segmentedFeatureFlags)->isActive());
    }

    /**
     * Test the both of the two segment rules matches.
     *
     * @return void
     */
    public function test_it_returns_the_feature_enabled_when_all_segment_rules_matches(): void
    {
        $featureFlags = $this->seedOneActiveFeatureFlagWithASegmentWithTwoRules();

        $this->assertCount(1, $featureFlags);
        $this->assertTrue(current($featureFlags)->isActive());

        $featureSegmentationService = new FeatureSegmentationService();

        $featuritUserContext = new FeaturitUserContext('12345', null, '127.0.0.1');

        $segmentedFeatureFlags = $featureSegmentationService->execute(
            $featureFlags,
            $featuritUserContext
        );

        $this->assertTrue(current($segmentedFeatureFlags)->isActive());
    }

    /**
     * Test the one or two of the two segments matches.
     *
     * @return void
     */
    public function test_it_returns_the_feature_enabled_when_one_of_the_two_segments_matches(): void
    {
        $featureFlags = $this->seedOneActiveFeatureFlagWithTwoSegments();

        $this->assertCount(1, $featureFlags);
        $this->assertTrue(current($featureFlags)->isActive());

        $featureSegmentationService = new FeatureSegmentationService();

        $featuritUserContext = new FeaturitUserContext('12345', null, null);

        $segmentedFeatureFlags = $featureSegmentationService->execute(
            $featureFlags,
            $featuritUserContext
        );

        $this->assertTrue(current($segmentedFeatureFlags)->isActive());

        $featuritUserContext = new FeaturitUserContext(null, null, '127.0.0.1');

        $segmentedFeatureFlags = $featureSegmentationService->execute(
            $featureFlags,
            $featuritUserContext
        );

        $this->assertTrue(current($segmentedFeatureFlags)->isActive());

        $featuritUserContext = new FeaturitUserContext('12345', null, '127.0.0.1');

        $segmentedFeatureFlags = $featureSegmentationService->execute(
            $featureFlags,
            $featuritUserContext
        );

        $this->assertTrue(current($segmentedFeatureFlags)->isActive());
    }

    /**
     * Test none of the two segments matches.
     *
     * @return void
     */
    public function test_no_segment_matches(): void
    {
        $featureFlags = $this->seedOneActiveFeatureFlagWithTwoSegments();

        $this->assertCount(1, $featureFlags);
        $this->assertTrue(current($featureFlags)->isActive());

        $featureSegmentationService = new FeatureSegmentationService();

        $featuritUserContext = new FeaturitUserContext('11111', null, '192.168.1.1');

        $segmentedFeatureFlags = $featureSegmentationService->execute(
            $featureFlags,
            $featuritUserContext
        );

        $this->assertFalse(current($segmentedFeatureFlags)->isActive());
    }

    /**
     * Test rollout percentage < rollout bucket calculator.
     *
     * @return void
     */
    public function test_it_returns_the_feature_disabled_when_rollout_percentage_smaller_than_rollout_bucket(): void
    {
        $featureFlags = $this->seedOneActiveFeatureFlagWithASegmentWithOneUserIdRuleAndRolloutPercentage50();

        $this->assertCount(1, $featureFlags);
        $this->assertTrue(current($featureFlags)->isActive());

        $mockBucketDistributor = $this->createMock(BucketDistributor::class);
        $mockBucketDistributor->method('distribute')->willReturn(100);

        $featureSegmentationService = new FeatureSegmentationService([], $mockBucketDistributor);

        $featuritUserContext = new FeaturitUserContext('12345', null, null);

        $segmentedFeatureFlags = $featureSegmentationService->execute(
            $featureFlags,
            $featuritUserContext
        );

        $this->assertFalse(current($segmentedFeatureFlags)->isActive());
    }

    /**
     * Test rollout percentage >= rollout bucket calculator.
     *
     * @return void
     */
    public function test_it_returns_the_feature_enabled_when_rollout_percentage_greater_or_equal_than_rollout_bucket(): void
    {
        $featureFlags = $this->seedOneActiveFeatureFlagWithASegmentWithOneUserIdRuleAndRolloutPercentage50();

        $this->assertCount(1, $featureFlags);
        $this->assertTrue(current($featureFlags)->isActive());

        $mockRolloutBucketCalculator = $this->createMock(BucketDistributor::class);
        $mockRolloutBucketCalculator->method('distribute')->willReturn(1, 50);

        $featureSegmentationService = new FeatureSegmentationService([], $mockRolloutBucketCalculator);

        $featuritUserContext = new FeaturitUserContext('12345', null, null);

        $segmentedFeatureFlags = $featureSegmentationService->execute(
            $featureFlags,
            $featuritUserContext
        );

        $this->assertTrue(current($segmentedFeatureFlags)->isActive());

        $segmentedFeatureFlags = $featureSegmentationService->execute(
            $featureFlags,
            $featuritUserContext
        );

        $this->assertTrue(current($segmentedFeatureFlags)->isActive());
    }

    private function seedOneSimpleFeatureFlag(): array
    {
        $featureFlag = new FeatureFlag(
            'Simple Feature',
            false,
            BaseAttributes::USER_ID,
            [],
            []
        );

        return [
            $featureFlag->name() => $featureFlag,
        ];
    }

    private function seedOneActiveFeatureFlagWithASegmentWithOneUserIdRule(): array
    {
        $userIdAttribute = BaseAttributes::USER_ID;

        $featureFlagSegment = new FeatureFlagSegment(
            $userIdAttribute,
            100,
            [
                new StringSegmentRule(
                    $userIdAttribute,
                    StringOperators::EQUALS,
                    '12345'
                )
            ],
            []
        );

        $featureFlag = new FeatureFlag(
            'Active Feature',
            true,
            $userIdAttribute,
            [$featureFlagSegment],
            []
        );

        return [
            $featureFlag->name() => $featureFlag,
        ];
    }

    private function seedOneActiveFeatureFlagWithASegmentWithOneCustomAttributeRule(): array
    {
        $userIdAttribute = BaseAttributes::USER_ID;
        $emailAttribute = 'email';

        $featureFlagSegment = new FeatureFlagSegment(
            $userIdAttribute,
            100,
            [
                new StringSegmentRule(
                    $emailAttribute,
                    StringOperators::ENDS_WITH,
                    '@featurit.com'
                )
            ],
            []
        );

        $featureFlag = new FeatureFlag(
            'Active Feature',
            true,
            $userIdAttribute,
            [$featureFlagSegment],
            []
        );

        return [
            $featureFlag->name() => $featureFlag,
        ];
    }

    private function seedOneActiveFeatureFlagWithASegmentWithTwoRules(): array
    {
        $userIdAttribute = BaseAttributes::USER_ID;
        $ipAddressAttribute = BaseAttributes::IP_ADDRESS;

        $featureFlagSegment = new FeatureFlagSegment(
            $userIdAttribute,
            100,
            [
                new StringSegmentRule(
                    $userIdAttribute,
                    StringOperators::EQUALS,
                    '12345'
                ),
                new StringSegmentRule(
                    $ipAddressAttribute,
                    StringOperators::EQUALS,
                    '127.0.0.1'
                )
            ],
            []
        );

        $featureFlag = new FeatureFlag(
            'Active Feature',
            true,
            $userIdAttribute,
            [$featureFlagSegment],
            []
        );

        return [
            $featureFlag->name() => $featureFlag,
        ];
    }

    private function seedOneActiveFeatureFlagWithTwoSegments(): array
    {
        $userIdAttribute = BaseAttributes::USER_ID;
        $ipAddressAttribute = BaseAttributes::IP_ADDRESS;

        $userIdFeatureFlagSegment = new FeatureFlagSegment(
            $userIdAttribute,
            100,
            [
                new StringSegmentRule(
                    $userIdAttribute,
                    StringOperators::EQUALS,
                    '12345'
                )
            ],
            []
        );

        $ipAddressFeatureFlagSegment = new FeatureFlagSegment(
            $userIdAttribute,
            100,
            [
                new StringSegmentRule(
                    $ipAddressAttribute,
                    StringOperators::EQUALS,
                    '127.0.0.1'
                )
            ],
            []
        );

        $featureFlag = new FeatureFlag(
            'Active Feature',
            true,
            $userIdAttribute,
            [
                $userIdFeatureFlagSegment,
                $ipAddressFeatureFlagSegment,
            ],
            []
        );

        return [
            $featureFlag->name() => $featureFlag,
        ];
    }

    private function seedOneActiveFeatureFlagWithASegmentWithOneUserIdRuleAndRolloutPercentage50(): array
    {
        $userIdAttribute = BaseAttributes::USER_ID;

        $featureFlagSegment = new FeatureFlagSegment(
            $userIdAttribute,
            50,
            [
                new StringSegmentRule(
                    $userIdAttribute,
                    StringOperators::EQUALS,
                    '12345'
                )
            ],
            []
        );

        $featureFlag = new FeatureFlag(
            'Active Feature',
            true,
            $userIdAttribute,
            [$featureFlagSegment],
            []
        );

        return [
            $featureFlag->name() => $featureFlag,
        ];
    }
}
