<?php

namespace Featurit\Client\Modules\Segmentation\Services;

use Featurit\Client\Modules\Segmentation\Entities\FeatureFlag;
use Featurit\Client\Modules\Segmentation\Entities\FeatureFlagVersion;
use Featurit\Client\Modules\Segmentation\FeaturitUserContext;
use Featurit\Client\Modules\Segmentation\Services\BucketDistributors\BucketDistributor;
use Featurit\Client\Modules\Segmentation\Services\BucketDistributors\MurmurBucketDistributor;

class FeatureFlagVersionSelector
{
    private BucketDistributor $bucketDistributor;

    public function __construct(
        BucketDistributor $bucketDistributor = null,
    )
    {
        $this->bucketDistributor = $bucketDistributor ?? new MurmurBucketDistributor();
    }

    /**
     * A -> 70%
     * B -> 10%
     * C -> 20%
     * Result -> 75
     * SelectedFeatureFlagVersion -> B
     *
     * Current implementation is order dependant on the FeatureFlagVersions.
     *
     * @param FeatureFlag $featureFlag
     * @param FeaturitUserContext $featuritUserContext
     * @return FeatureFlagVersion|null
     */
    public function select(
        FeatureFlag $featureFlag,
        FeaturitUserContext $featuritUserContext
    ): ?FeatureFlagVersion
    {
        $featureFlagName = $featureFlag->name();
        $distributionAttributeName = $featureFlag->distributionAttribute();
        $distributionAttributeValue = $featuritUserContext->getAttribute($distributionAttributeName);

        $distributionCalculationResult = $this->bucketDistributor->distribute($featureFlagName, $distributionAttributeValue);

        $previousDistributionPercentage = 0;
        foreach ($featureFlag->featureFlagVersions() as $featureFlagVersion) {
            $distributionPercentage = $featureFlagVersion->featureFlagDistributionPercentage();

            if ($distributionCalculationResult > ($distributionPercentage + $previousDistributionPercentage)) {
                $previousDistributionPercentage = $distributionPercentage;
                continue;
            }

            return $featureFlagVersion;
        }

        return null;
    }
}
