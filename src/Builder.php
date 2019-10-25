<?php
/**
 * Author: Adrian Szuszkiewicz <me@imper.info>
 * Github: https://github.com/imper86
 * Date: 23.10.2019
 * Time: 16:36
 */

namespace Imper86\HttpClientBuilder;

use Http\Client\Common\Plugin;
use Http\Client\Common\Plugin\Cache\Generator\HeaderCacheKeyGenerator;
use Http\Client\Common\PluginClientFactory;
use Http\Client\HttpClient;
use Http\Discovery\HttpClientDiscovery;
use Http\Discovery\Psr17FactoryDiscovery;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;

class Builder implements BuilderInterface
{
    /**
     * @var HttpClient
     */
    private $httpClient;
    /**
     * @var HttpClient|null
     */
    private $pluginClient;
    /**
     * @var RequestFactoryInterface
     */
    private $requestFactory;
    /**
     * @var StreamFactoryInterface
     */
    private $streamFactory;
    /**
     * @var UriFactoryInterface
     */
    private $uriFactory;
    /**
     * @var bool
     */
    private $httpClientModified = true;
    /**
     * @var Plugin[]
     */
    private $plugins = [];
    /**
     * @var Plugin\CachePlugin|null
     */
    private $cachePlugin;

    public function __construct(
        ?HttpClient $httpClient = null,
        ?RequestFactoryInterface $requestFactory = null,
        ?StreamFactoryInterface $streamFactory = null,
        ?UriFactoryInterface $uriFactory = null
    )
    {
        $this->httpClient = $httpClient ?: HttpClientDiscovery::find();
        $this->requestFactory = $requestFactory ?: Psr17FactoryDiscovery::findRequestFactory();
        $this->streamFactory = $streamFactory ?: Psr17FactoryDiscovery::findStreamFactory();
        $this->uriFactory = $uriFactory ?: Psr17FactoryDiscovery::findUrlFactory();
    }

    public function getHttpClient(): HttpClient
    {
        if ($this->httpClientModified) {
            $this->httpClientModified = false;

            $plugins = $this->plugins;

            if ($this->cachePlugin) {
                $plugins[] = $this->cachePlugin;
            }

            $this->pluginClient = (new PluginClientFactory())->createClient($this->httpClient, $plugins);
        }

        return $this->pluginClient;
    }

    public function setHttpClient(HttpClient $httpClient): void
    {
        $this->httpClient = $httpClient;
        $this->httpClientModified = true;
    }

    /**
     * @return RequestFactoryInterface
     */
    public function getRequestFactory(): RequestFactoryInterface
    {
        return $this->requestFactory;
    }

    /**
     * @return StreamFactoryInterface
     */
    public function getStreamFactory(): StreamFactoryInterface
    {
        return $this->streamFactory;
    }

    /**
     * @return UriFactoryInterface
     */
    public function getUriFactory(): UriFactoryInterface
    {
        return $this->uriFactory;
    }

    public function addPlugin(Plugin $plugin): void
    {
        $this->plugins[] = $plugin;
        $this->httpClientModified = true;
    }

    public function removePlugin(string $fqcn): void
    {
        foreach ($this->plugins as $key => $plugin) {
            if ($plugin instanceof $fqcn) {
                unset($this->plugins[$key]);
                $this->httpClientModified = true;
            }
        }
    }

    public function addCache(CacheItemPoolInterface $pool, array $config): void
    {
        if (!isset($config['cache_key_generator'])) {
            $config['cache_key_generator'] = new HeaderCacheKeyGenerator([
                'Authorization',
                'Cookie',
                'Accept',
                'Content-Type'
            ]);
        }

        $this->cachePlugin = Plugin\CachePlugin::clientCache($pool, $this->streamFactory, $config);
        $this->httpClientModified = true;
    }

    public function removeCache(): void
    {
        $this->cachePlugin = null;
        $this->httpClientModified = true;
    }
}
