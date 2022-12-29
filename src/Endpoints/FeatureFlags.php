<?php

namespace Featurit\Client\Endpoints;

use Exception;
use Featurit\Client\Featurit;
use Featurit\Client\HttpClient\Message\ResponseMediator;
use Featurit\Client\Modules\Segmentation\Services\Hydrators\FeatureFlagsHydrator;

class FeatureFlags
{
    private Featurit $featurit;

    public function __construct(Featurit $featurit)
    {
        $this->featurit = $featurit;
    }

    /**
     * @throws Exception
     * @throws \Http\Client\Exception
     */
    public function all(): array
    {
        $featuritUserContext = $this->featurit->getUserContext();

        $cacheKey = "featureFlags_{$this->featurit->getApiKey()}";

        if ($this->featurit->getCache()->has($cacheKey)) {
            $featureFlagArrayResponse = $this->featurit->getCache()->get($cacheKey);

            $featureFlags = (new FeatureFlagsHydrator())->hydrate($featureFlagArrayResponse);

            return $this->featurit->getFeatureSegmentationService()->execute(
                $featureFlags,
                $featuritUserContext
            );
        }

        $featureFlagsApiResponse = $this->featurit->getHttpClient()
            ->get("/feature-flags");

        if ($featureFlagsApiResponse->getStatusCode() != 200) {
            throw new Exception("Url not found");
        }

        $featureFlagArrayResponse = ResponseMediator::getContent($featureFlagsApiResponse);

        $this->featurit->getCache()->set($cacheKey, $featureFlagArrayResponse);

        $featureFlags = (new FeatureFlagsHydrator())->hydrate($featureFlagArrayResponse);

        return $this->featurit->getFeatureSegmentationService()->execute(
            $featureFlags,
            $featuritUserContext
        );
    }

    public function isActive($featureFlagName): bool
    {
        try {
            $featureFlags = $this->all();

            // If you ask for an non-existing feature flag, it returns false by default
            if (! array_key_exists($featureFlagName, $featureFlags)) {
                return false;
            }

            return $featureFlags[$featureFlagName]->isActive();
        } catch (Exception | \Http\Client\Exception $exception) {
            // If the server couldn't be contacted, we should return the value on cache.
            // In this case, the cache couldn't be hit, maybe because it's expired or because
            // the feature has never been there.
            // If it's expired we should unexpire it for another TTL, as it's better to see an old cache than an
            // exception due to FeaturIT downtime.

            // TODO: Implement the proper business logic.

            return false;
        }
    }
}