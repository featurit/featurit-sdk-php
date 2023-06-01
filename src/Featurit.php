<?php

namespace Featurit\Client;

use Featurit\Client\Endpoints\FeatureFlags;
use Featurit\Client\HttpClient\ClientBuilder;
use Featurit\Client\Modules\Segmentation\DefaultFeaturitUserContext;
use Featurit\Client\Modules\Segmentation\DefaultFeaturitUserContextProvider;
use Featurit\Client\Modules\Segmentation\FeaturitUserContext;
use Featurit\Client\Modules\Segmentation\FeaturitUserContextProvider;
use Featurit\Client\Modules\Segmentation\Services\FeatureSegmentationService;
use Http\Client\Common\HttpMethodsClientInterface;
use Http\Client\Common\Plugin\BaseUriPlugin;
use Http\Client\Common\Plugin\HeaderDefaultsPlugin;
use Http\Discovery\Psr17FactoryDiscovery;
use Http\Message\UriFactory;
use Psr\SimpleCache\CacheInterface;

class Featurit
{
    private string $tenantIdentifier;
    private string $apiKey;

    private FeaturitUserContextProvider $featuritUserContextProvider;
    private ClientBuilder $clientBuilder;
    private CacheInterface $cache;
    private CacheInterface $backupCache;
    private FeatureSegmentationService $featureSegmentationService;
    private LocalCacheFactory $localCacheFactory;

    public function __construct(
        string                          $tenantIdentifier,
        string                          $apiKey,
        int                             $cacheTtlMinutes = FeaturitBuilder::DEFAULT_CACHE_TTL_MINUTES,
        FeaturitUserContextProvider     $featuritUserContextProvider = null,
        CacheInterface                  $cache = null,
        ClientBuilder                   $clientBuilder = null,
        UriFactory                      $uriFactory = null,
        FeaturitUserContext             $featuritUserContext = null,
    ) {
        $this->tenantIdentifier = $tenantIdentifier;
        $this->apiKey = $apiKey;

        $this->setFeaturitUserContextProvider($featuritUserContext, $featuritUserContextProvider);

        $this->localCacheFactory = new LocalCacheFactory();

        $this->setCache($cache, $cacheTtlMinutes);

        $this->setHttpClientBuilder($clientBuilder, $uriFactory);

        $this->featureSegmentationService = new FeatureSegmentationService();
    }

    /**
     * @throws HttpClient\Exceptions\InvalidApiKeyException
     */
    public function isActive(string $featureName): bool
    {
        return $this->featureFlags()->isActive($featureName);
    }

    /**
     * @throws HttpClient\Exceptions\InvalidApiKeyException
     */
    public function version(string $featureName): string
    {
        return $this->featureFlags()->version($featureName);
    }

    public function featureFlags(): FeatureFlags
    {
        return new Endpoints\FeatureFlags($this);
    }

    public function getUserContext(): FeaturitUserContext
    {
        return $this->featuritUserContextProvider->getUserContext();
    }

    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    public function getCache(): CacheInterface
    {
        return $this->cache;
    }

    public function getBackupCache(): CacheInterface
    {
        return $this->backupCache;
    }

    public function getHttpClient(): HttpMethodsClientInterface
    {
        return $this->clientBuilder->getHttpClient();
    }

    public function getFeatureSegmentationService(): FeatureSegmentationService
    {
        return $this->featureSegmentationService;
    }

    public function setUserContext(FeaturitUserContext $featuritUserContext): void
    {
        $this->setFeaturitUserContextProvider($featuritUserContext);
    }

    /**
     * @param CacheInterface|null $cache
     * @param int $cacheTtlMinutes
     * @return void
     */
    private function setCache(?CacheInterface $cache, int $cacheTtlMinutes): void
    {
        if (is_null($cache)) {
            $cache = $this->localCacheFactory->setLocalCache($cacheTtlMinutes, 'cache' , true);
        }

        /**
         * Backup cache will be used when there's some problem with the FeaturIT API.
         */
        $this->backupCache = $this->localCacheFactory->setLocalCache(0, 'backup', false);

        $this->cache = $cache;
    }

    /**
     * @param ClientBuilder|null $clientBuilder
     * @param UriFactory|null $uriFactory
     * @return void
     */
    private function setHttpClientBuilder(?ClientBuilder $clientBuilder, ?UriFactory $uriFactory): void
    {
        $this->clientBuilder = $clientBuilder ?: new ClientBuilder();
        $uriFactory = $uriFactory ?: Psr17FactoryDiscovery::findUriFactory();

        $this->clientBuilder->addPlugin(
            new BaseUriPlugin(
                $uriFactory->createUri("https://{$this->tenantIdentifier}.featurit.com/api/v1/{$this->apiKey}")
            )
        );

        $this->clientBuilder->addPlugin(
            new HeaderDefaultsPlugin(
                [
                    'User-Agent' => 'FeaturIT',
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ]
            )
        );
    }

    /**
     * @param FeaturitUserContext|null $featuritUserContext
     * @param FeaturitUserContextProvider|null $featuritUserContextProvider
     * @return void
     */
    public function setFeaturitUserContextProvider(?FeaturitUserContext $featuritUserContext = null, ?FeaturitUserContextProvider $featuritUserContextProvider = null): void
    {
        if (! is_null($featuritUserContext)) {
            $this->featuritUserContextProvider = new DefaultFeaturitUserContextProvider($featuritUserContext);

            return;
        }

        if (is_null($featuritUserContextProvider)) {
            $featuritUserContextProvider = new DefaultFeaturitUserContextProvider(
                new DefaultFeaturitUserContext(null, null, null)
            );
        }

        $this->featuritUserContextProvider = $featuritUserContextProvider;
    }
}