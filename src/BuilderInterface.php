<?php
/**
 * Author: Adrian Szuszkiewicz <me@imper.info>
 * Github: https://github.com/imper86
 * Date: 23.10.2019
 * Time: 16:33
 */

namespace Imper86\HttpClientBuilder;

use Http\Client\Common\Plugin;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;

/**
 * Interface BuilderInterface
 * @package Imper86\HttpClientBuilder
 */
interface BuilderInterface
{
    /**
     * Returns fully prepared http client
     *
     * @return ClientInterface
     */
    public function getHttpClient(): ClientInterface;

    /**
     * @return RequestFactoryInterface
     */
    public function getRequestFactory(): RequestFactoryInterface;

    /**
     * @return StreamFactoryInterface
     */
    public function getStreamFactory(): StreamFactoryInterface;

    /**
     * @return UriFactoryInterface
     */
    public function getUriFactory(): UriFactoryInterface;

    /**
     * You can add any plugin you want here
     *
     * @param Plugin $plugin
     */
    public function addPlugin(Plugin $plugin): void;

    /**
     * Use fully qualified class name to remove a plugin
     *
     * @param string $fqcn
     */
    public function removePlugin(string $fqcn): void;

    /**
     * Adds cache plugin with given pool
     *
     * @param CacheItemPoolInterface $pool
     * @param array $config
     */
    public function addCache(CacheItemPoolInterface $pool, array $config): void;

    /**
     * Removes cache plugin
     */
    public function removeCache(): void;
}
