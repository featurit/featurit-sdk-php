<?php

namespace Featurit\Client\Tests\Modules\Segmentation\Services;

use Featurit\Client\Modules\Segmentation\DefaultFeaturitUserContext as FeaturitUserContext;
use Featurit\Client\Modules\Segmentation\Entities\FeatureFlag;
use Featurit\Client\Modules\Segmentation\Entities\FeatureFlagVersion;
use Featurit\Client\Modules\Segmentation\Services\BucketDistributors\BucketDistributor;
use Featurit\Client\Modules\Segmentation\Services\FeatureFlagVersionSelector;
use PHPUnit\Framework\TestCase;

class FeatureFlagVersionSelectorTest extends TestCase
{
    /**
     * Equals.
     *
     * @return void
     */
    public function test_one_particular_case(): void
    {
        $featureFlag = new FeatureFlag(
            "Feat",
            true,
            "userId",
            [],
            [
                new FeatureFlagVersion("v1", 70),
                new FeatureFlagVersion("v2", 20),
                new FeatureFlagVersion("v3", 10),
            ]
        );

        $featuritUserContext = new FeaturitUserContext('1234', null, null);

        $mockBucketDistributor = $this->createMock(BucketDistributor::class);
        $mockBucketDistributor->method('distribute')->willReturn(75);

        $featureFlagVersionSelector = new FeatureFlagVersionSelector($mockBucketDistributor);

        $selectedFeatureFlagVersion = $featureFlagVersionSelector->select($featureFlag, $featuritUserContext);

        $this->assertEquals("v2", $selectedFeatureFlagVersion->name());
    }
}
