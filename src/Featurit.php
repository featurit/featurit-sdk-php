<?php

namespace Featurit\Client;

use Featurit\Client\Endpoints\FeatureFlags;
use Featurit\Client\HttpClient\ClientBuilder;
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

    private ClientBuilder $clientBuilder;
    private CacheInterface $cache;

    public function __construct(
        string         $tenantIdentifier,
        string         $apiKey,
        int            $cacheTtlMinutes = FeaturitBuilder::DEFAULT_CACHE_TTL_MINUTES,
        CacheInterface $cache = null,
        ClientBuilder  $clientBuilder = null,
        UriFactory     $uriFactory = null
    ) {
        $this->tenantIdentifier = $tenantIdentifier;
        $this->apiKey = $apiKey;

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

    /**
     * @throws \Http\Client\Exception
     */
    public function isActive(string $featureName): bool
    {
        return $this->featureFlags()->isActive($featureName);
    }

    public function featureFlags(): FeatureFlags
    {
        return new Endpoints\FeatureFlags($this);
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
}