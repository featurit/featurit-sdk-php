<?php

namespace Featurit\Client\Modules\Analytics\Services;

use DateTime;
use Featurit\Client\Modules\Analytics\AnalyticsBucket;
use Featurit\Client\Modules\Analytics\Exceptions\CantSendAnalyticsToServerException;
use Featurit\Client\Modules\Segmentation\Entities\FeatureFlag;
use Featurit\Client\Modules\Segmentation\FeaturitUserContext;
use Psr\SimpleCache\CacheInterface;

class FeatureAnalyticsService
{
    public function __construct(
        private CacheInterface $analyticsCache,
        private AnalyticsSender $analyticsSender,
        private int $sendAnalyticsIntervalMinutes,
    )
    {
    }

    public function registerFeatureFlagRequest(
        FeatureFlag $featureFlag,
        FeaturitUserContext $featuritUserContext,
        DateTime $currentTime = null,
    ): void
    {
        if (! is_null($currentTime)) {
            $now = $currentTime;
        } else {
            $now = new DateTime();
        }

        $analyticsCacheKey = "analytics_bucket";

        // Get or create the analytics bucket.
        if ($this->analyticsCache->has($analyticsCacheKey)) {
            dump("Getting Analytics from cache");
            $analyticsBucket = $this->analyticsCache->get($analyticsCacheKey);
            dump("Bucket Start DateTime: " . $analyticsBucket->startDateTime()->format('c'));
            dump("Now: " . $now->format('c'));
        } else {
            dump("Creating a new bucket");
            $analyticsBucket = new AnalyticsBucket($now);
        }

        $analyticsBucket->addFeatureFlagRequest($featureFlag, $featuritUserContext, $now);
        dump("Time diff: " . $analyticsBucket->startDateTime()->diff($now)->i);
        // TODO: This approach can have problems due to sending big payloads to the server in case of failure or huge traffic.
        if ($analyticsBucket->startDateTime()->diff($now)->i >= $this->sendAnalyticsIntervalMinutes) {
            try {
                $analyticsBucket->closeBucket($now);
                $this->analyticsSender->sendAnalyticsBucket($analyticsBucket);

                $this->analyticsCache->delete($analyticsCacheKey);
                dump("Analytics removed from cache");
            } catch (CantSendAnalyticsToServerException $exception) {
                dump("Error sending analytics to the API");
                $analyticsBucket->openBucket();
                $this->analyticsCache->set($analyticsCacheKey, $analyticsBucket);
            }
        } else {
            $this->analyticsCache->set($analyticsCacheKey, $analyticsBucket);
        }
    }
}