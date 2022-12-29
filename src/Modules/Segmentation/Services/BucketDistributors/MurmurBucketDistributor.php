<?php

namespace Featurit\Client\Modules\Segmentation\Services\BucketDistributors;

use lastguest\Murmur;

class MurmurBucketDistributor implements BucketDistributor
{
    public function distribute(string $featureName, string|null $featureRolloutAttributeValue = ''): int
    {
        return Murmur::hash3_int("${featureName}:${featureRolloutAttributeValue}") % 100 + 1;
    }
}
