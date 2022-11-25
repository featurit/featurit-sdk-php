<?php

namespace Featurit\Client\Endpoints;

use Exception;
use Featurit\Client\Featurit;
use Featurit\Client\HttpClient\Message\ResponseMediator;

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
        $cacheKey = "featureFlags_{$this->featurit->getApiKey()}";

        if ($this->featurit->getCache()->has($cacheKey)) {
            return $this->featurit->getCache()->get($cacheKey);
        }

        $featureFlagsApiResponse = $this->featurit->getHttpClient()->get('/feature-flags');

        if ($featureFlagsApiResponse->getStatusCode() != 200) {
            throw new Exception("Url not found");
        }

        $featureFlagArrayResponse = ResponseMediator::getContent($featureFlagsApiResponse);

        $this->featurit->getCache()->set($cacheKey, $featureFlagArrayResponse);

        return $featureFlagArrayResponse;
    }

    /**
     * @throws \Http\Client\Exception
     */
    public function isActive($featureFlagName): bool
    {
        $featureFlags = $this->all();
        if (!array_key_exists($featureFlagName, $featureFlags)) {
            return false;
        }

        return $featureFlags[$featureFlagName];
    }
}