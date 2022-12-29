<?php

namespace Featurit\Client;

use Featurit\Client\Endpoints\FeatureFlags;
use Featurit\Client\HttpClient\ClientBuilder;
use Featurit\Client\Modules\Segmentation\DefaultFeaturitUserContextProvider;
use Featurit\Client\Modules\Segmentation\FeaturitUserContext;
use Featurit\Client\Modules\Segmentation\FeaturitUserContextProvider;
use Featurit\Client\Modules\Segmentation\Services\FeatureSegmentationService;
use Http\Client\Common\HttpMethodsClientInterface;
use Http\Client\Common\Plugin\BaseUriPlugin;
use Http\Client\Common\Plugin\HeaderDefaultsPlugin;
use Http\Discovery\Psr17FactoryDiscovery;
use Http\Message\UriFactory;
use Laminas\Cache\Psr\SimpleCache\SimpleCacheDecorator;
use Laminas\Cache\Storage\Adapter\Filesystem;
use Laminas\Cache\Storage\Plugin\ExceptionHandler;
use Laminas\Cache\Storage\Plugin\Serializer;
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

    public function __construct(
        string                          $tenantIdentifier,
        string                          $apiKey,
        int                             $cacheTtlMinutes = FeaturitBuilder::DEFAULT_CACHE_TTL_MINUTES,
        FeaturitUserContextProvider     $featuritUserContextProvider = null,
        CacheInterface                  $cache = null,
        ClientBuilder                   $clientBuilder = null,
        UriFactory                      $uriFactory = null
    ) {
        $this->tenantIdentifier = $tenantIdentifier;
        $this->apiKey = $apiKey;

        $this->featuritUserContextProvider = $featuritUserContextProvider ?: new DefaultFeaturitUserContextProvider();

        $this->setCache($cache, $cacheTtlMinutes);

        $this->setHttpClientBuilder($clientBuilder, $uriFactory);

        $this->featureSegmentationService = new FeatureSegmentationService();
    }

    public function isActive(string $featureName): bool
    {
        return $this->featureFlags()->isActive($featureName);
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

    /**
     * @param CacheInterface|null $cache
     * @param int $cacheTtlMinutes
     * @return void
     */
    private function setCache(?CacheInterface $cache, int $cacheTtlMinutes): void
    {
        if (is_null($cache)) {
            $cache = $this->setLocalCache($cacheTtlMinutes);
        }

        /**
         * Backup cache will be used when there's some problem with the FeaturIT API.
         */
        $this->backupCache = $this->setLocalCache(0);

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
     * @param int $cacheTtlMinutes
     * @return CacheInterface
     */
    private function setLocalCache(int $cacheTtlMinutes): SimpleCacheDecorator
    {
        $storage = new Filesystem();

        $plugin = new ExceptionHandler();
        $plugin->getOptions()->setThrowExceptions(true);

        $storage->addPlugin($plugin);

        $plugin = new Serializer();
        $storage->addPlugin($plugin);

        $storage->getOptions()->setTtl($cacheTtlMinutes * 60);

        $cacheDirName = join(DIRECTORY_SEPARATOR, [dirname(__FILE__), '..', 'cache']);

        if (!file_exists($cacheDirName)) {
            mkdir($cacheDirName, 0755, true);
        }

        $storage->getOptions()->setCacheDir($cacheDirName);

        return new SimpleCacheDecorator($storage);
    }
}