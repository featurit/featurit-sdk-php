<?php

namespace Featurit\Client;

use Featurit\Client\HttpClient\ClientBuilder;
use Featurit\Client\Modules\Segmentation\FeaturitUserContext;
use Featurit\Client\Modules\Segmentation\FeaturitUserContextProvider;
use Http\Message\UriFactory;
use Psr\SimpleCache\CacheInterface;

class FeaturitBuilder
{
    public const DEFAULT_CACHE_TTL_MINUTES = 5;
    public const DEFAULT_SEND_ANALYTICS_INTERVAL_MINUTES = 1;

    private string $tenantIdentifier;
    private string $apiKey;
    private bool $isAnalyticsModuleEnabled = false;
    private bool $isTrackingModuleEnabled = false;
    private int $cacheTtlMinutes = self::DEFAULT_CACHE_TTL_MINUTES;
    private int $sendAnalyticsIntervalMinutes = self::DEFAULT_SEND_ANALYTICS_INTERVAL_MINUTES;
    private FeaturitUserContextProvider $featuritUserContextProvider;
    private CacheInterface $cache;
    private ClientBuilder $httpClientBuilder;
    private UriFactory $uriFactory;
    private FeaturitUserContext $featuritUserContext;

    public function setTenantIdentifier(string $tenantIdentifier): FeaturitBuilder
    {
        $this->tenantIdentifier = $tenantIdentifier;

        return $this;
    }

    public function setApiKey(string $apiKey): FeaturitBuilder
    {
        $this->apiKey = $apiKey;

        return $this;
    }

    public function setIsAnalyticsModuleEnabled(bool $isAnalyticsModuleEnabled): FeaturitBuilder
    {
        $this->isAnalyticsModuleEnabled = $isAnalyticsModuleEnabled;

        return $this;
    }

    public function setIsTrackingModuleEnabled(bool $isTrackingModuleEnabled): FeaturitBuilder
    {
        $this->isTrackingModuleEnabled = $isTrackingModuleEnabled;

        return $this;
    }

    public function setCacheTtlMinutes(int $cacheTtlMinutes): FeaturitBuilder
    {
        $this->cacheTtlMinutes = $cacheTtlMinutes;

        return $this;
    }

    public function setSendAnalyticsIntervalMinutes(int $sendAnalyticsIntervalMinutes): FeaturitBuilder
    {
        $this->sendAnalyticsIntervalMinutes = $sendAnalyticsIntervalMinutes;

        return $this;
    }

    public function setFeaturitUserContextProvider(FeaturitUserContextProvider $featuritUserContextProvider): FeaturitBuilder
    {
        $this->featuritUserContextProvider = $featuritUserContextProvider;

        return $this;
    }

    public function setCache(CacheInterface $cache): FeaturitBuilder
    {
        $this->cache = $cache;

        return $this;
    }

    public function setHttpClientBuilder(ClientBuilder $clientBuilder): FeaturitBuilder
    {
        $this->httpClientBuilder = $clientBuilder;

        return $this;
    }

    public function setUriFactory(UriFactory $uriFactory): FeaturitBuilder
    {
        $this->uriFactory = $uriFactory;

        return $this;
    }

    public function setUserContext(FeaturitUserContext $featuritUserContext): FeaturitBuilder
    {
        $this->featuritUserContext = $featuritUserContext;

        return $this;
    }

    /**
     * @throws \Exception
     */
    public function build(): Featurit
    {
        if (!isset($this->tenantIdentifier)) {
            throw new \Exception("Tenant Identifier is mandatory to build a FeaturIT Client");
        }

        if (!isset($this->apiKey)) {
            throw new \Exception("API Key is mandatory to build a FeaturIT Client");
        }

        return new Featurit(
            $this->tenantIdentifier,
            $this->apiKey,
            $this->cacheTtlMinutes,
            $this->featuritUserContextProvider ?? null,
            $this->cache ?? null,
            $this->httpClientBuilder ?? null,
            $this->uriFactory ?? null,
            $this->featuritUserContext ?? null,
            $this->isAnalyticsModuleEnabled,
            $this->sendAnalyticsIntervalMinutes,
            $this->isTrackingModuleEnabled,
        );
    }
}