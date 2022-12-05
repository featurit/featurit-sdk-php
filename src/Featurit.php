<?php

namespace Featurit\Client;

use Featurit\Client\Endpoints\FeatureFlags;
use Featurit\Client\HttpClient\ClientBuilder;
use Featurit\Client\Modules\Segmentation\DefaultFeaturitUserContextProvider;
use Featurit\Client\Modules\Segmentation\FeaturitUserContextProvider;
use Featurit\Client\Modules\Segmentation\FeaturitUserContext;
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

    public function getHttpClient(): HttpMethodsClientInterface
    {
        return $this->clientBuilder->getHttpClient();
    }

    /**
     * @param CacheInterface|null $cache
     * @param int $cacheTtlMinutes
     * @return void
     */
    private function setCache(?CacheInterface $cache, int $cacheTtlMinutes): void
    {
        if (is_null($cache)) {
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

            $cache = new SimpleCacheDecorator($storage);
        }

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
                $uriFactory->createUri("https://{$this->tenantIdentifier}.featurit.com/api/{$this->apiKey}")
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
}