<?php

namespace Featurit\Client\Endpoints;

use Featurit\Client\Featurit;
use Featurit\Client\HttpClient\Exceptions\InvalidApiKeyException;
use Featurit\Client\HttpClient\Exceptions\UnknownServerException;
use Featurit\Client\HttpClient\Message\ResponseMediator;
use Featurit\Client\Modules\Segmentation\ConstantCollections\BaseVersions;
use Featurit\Client\Modules\Segmentation\Services\Hydrators\FeatureFlagsHydrator;
use Psr\SimpleCache\InvalidArgumentException;

class FeatureFlags
{
    private Featurit $featurit;

    public function __construct(Featurit $featurit)
    {
        $this->featurit = $featurit;
    }

    /**
     * @throws InvalidApiKeyException
     */
    public function all(): array
    {
        $featuritUserContext = $this->featurit->getUserContext();

        $cacheKey = "featureFlags_{$this->featurit->getApiKey()}";
        $backupCacheKey = "backup_{$cacheKey}";

        try {
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

            if ($featureFlagsApiResponse->getStatusCode() == 404) {
                throw new InvalidApiKeyException("Invalid API Key");
            } else if ($featureFlagsApiResponse->getStatusCode() != 200) {
                throw new UnknownServerException("Something went wrong");
            }

            $featureFlagArrayResponse = ResponseMediator::getContent($featureFlagsApiResponse);

            $this->featurit->getCache()->set($cacheKey, $featureFlagArrayResponse);
            $this->featurit->getBackupCache()->set($backupCacheKey, $featureFlagArrayResponse);

            $featureFlags = (new FeatureFlagsHydrator())->hydrate($featureFlagArrayResponse);

            return $this->featurit->getFeatureSegmentationService()->execute(
                $featureFlags,
                $featuritUserContext
            );

        } catch (\Http\Client\Exception|UnknownServerException $exception) {

            try {
                if (!$this->featurit->getBackupCache()->has($backupCacheKey)) {
                    return [];
                }

                $featureFlagArrayResponse = $this->featurit->getBackupCache()->get($backupCacheKey);
                $featureFlags = (new FeatureFlagsHydrator())->hydrate($featureFlagArrayResponse);

                return $this->featurit->getFeatureSegmentationService()->execute(
                    $featureFlags,
                    $featuritUserContext
                );
            } catch (InvalidArgumentException $exception) {

                // This should never happen as we control the cache key name.
                return [];
            }
        } catch (InvalidArgumentException $exception) {

            // This should never happen as we control the cache key name.
            return [];
        }
    }

    /**
     * @throws InvalidApiKeyException
     */
    public function isActive(string $featureFlagName): bool
    {
        $featureFlags = $this->all();

        // If you ask for an non-existing feature flag, it returns false
        if (! array_key_exists($featureFlagName, $featureFlags)) {
            // TODO: Should we send analytics here?
            return false;
        }

        if ($this->featurit->isAnalyticsModuleEnabled()) {
            $this->featurit->getFeatureAnalyticsService()->registerFeatureFlagRequest(
                $featureFlags[$featureFlagName]
            );
        }

        return $featureFlags[$featureFlagName]->isActive();
    }

    /**
     * @throws InvalidApiKeyException
     */
    public function version(string $featureFlagName): string
    {
        $featureFlags = $this->all();

        // If you ask for an non-existing feature flag, it returns 'default'
        if (! array_key_exists($featureFlagName, $featureFlags)) {
            return BaseVersions::DEFAULT;
        }

        return $featureFlags[$featureFlagName]->selectedFeatureFlagVersion()->name();
    }
}