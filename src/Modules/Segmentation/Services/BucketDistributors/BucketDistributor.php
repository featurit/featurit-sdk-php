<?php

namespace Featurit\Client\Modules\Segmentation\Services\BucketDistributors;

interface BucketDistributor
{
    /**
     * @param string $featureName
     * @param string|null $featureRolloutAttributeValue
     * @return int
     */
    public function distribute(string $featureName, string|null $featureRolloutAttributeValue = ''): int;
}
