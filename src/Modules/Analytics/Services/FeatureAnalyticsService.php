<?php

namespace Featurit\Client\Modules\Analytics\Services;

use DateTime;
use Featurit\Client\Modules\Analytics\AnalyticsBucket;
use Featurit\Client\Modules\Analytics\Exceptions\CantSendAnalyticsToServerException;
use Featurit\Client\Modules\Segmentation\Entities\FeatureFlag;
use Psr\SimpleCache\CacheInterface;

class FeatureAnalyticsService
{
    public function __construct(
        private CacheInterface $analyticsCache,
        private AnalyticsSender $analyticsSender,
        private int $sendAnalyticsIntervalMinutes,
        private string $environmentKey,
    )
    {
    }

    public function registerFeatureFlagRequest(
        FeatureFlag $featureFlag,
        DateTime $currentTime = null,
    ): void
    {
        if (! is_null($currentTime)) {
            $now = $currentTime;
        } else {
            $now = new DateTime();
        }

        $analyticsCacheKey = "analytics_bucket_$this->environmentKey";

        // Get or create the analytics bucket.
        if ($this->analyticsCache->has($analyticsCacheKey)) {
            $analyticsBucket = $this->analyticsCache->get($analyticsCacheKey);
        } else {
            $analyticsBucket = new AnalyticsBucket($now);
        }

        $analyticsBucket->addFeatureFlagRequest($featureFlag, $currentTime);

        // TODO: This approach can have problems due to sending big payloads to the server in case of failure or huge traffic.
        if ($analyticsBucket->startDateTime()->diff($now)->i >= $this->sendAnalyticsIntervalMinutes) {
            try {
                $analyticsBucket->closeBucket($now);
                $this->analyticsSender->sendAnalyticsBucket($analyticsBucket);

                $this->analyticsCache->delete($analyticsCacheKey);
            } catch (CantSendAnalyticsToServerException $exception) {
                $analyticsBucket->openBucket();
                $this->analyticsCache->set($analyticsCacheKey, $analyticsBucket);
            }
        } else {
            $this->analyticsCache->set($analyticsCacheKey, $analyticsBucket);
        }
    }
}