<?php

namespace Featurit\Client\Tests\Modules\Segmentation\Services\Hydrators;

use Featurit\Client\Modules\Segmentation\Entities\FeatureFlag;
use Featurit\Client\Modules\Segmentation\Services\Hydrators\FeatureFlagsHydrator;
use PHPUnit\Framework\TestCase;

class FeatureFlagsHydratorTest extends TestCase
{
    /**
     * Equals.
     *
     * @return void
     */
    public function test_from_array_and_to_array_doesnt_change_the_object(): void
    {
        $featureFlagsHydrator = new FeatureFlagsHydrator();

        $featureFlagsArray = [
            "Feat" => [
                "name" => "Feat",
                "active" => false,
                "distribution_attribute" => "userId",
                "segments" => [
                    [
                        "rollout_attribute" => "userId",
                        "rollout_percentage" => 100,
                        "string_rules" => [
                            [
                                "attribute" => "userId",
                                "operator" => "EQUALS",
                                "value" => "1",
                            ],
                        ],
                        "number_rules" => [],
                    ],
                ],
                "versions" => [
                    [
                        "name" => "v1",
                        "distribution_percentage" => 0,
                    ],
                ],
            ],
        ];

        $featureFlags = $featureFlagsHydrator->hydrate($featureFlagsArray);

        $this->assertCount(1, $featureFlagsArray);

        $this->assertEquals($featureFlagsArray, array_map(function(FeatureFlag $featureFlag) {
            return $featureFlag->toArray();
        }, $featureFlags));
    }
}
