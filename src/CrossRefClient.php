<?php

/*
 * This file is part of CrossRef Client.
 *
 * (c) Renan de Lima Barbosa <renandelima@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RenanBr;

use GuzzleHttp;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use Kevinrob\GuzzleCache\CacheMiddleware;
use Kevinrob\GuzzleCache\Storage\Psr16CacheStorage;
use Kevinrob\GuzzleCache\Strategy\GreedyCacheStrategy;
use Psr\SimpleCache\CacheInterface;

class CrossRefClient
{
    const BASE_URI = 'https://api.crossref.org';
    const CACHE_TTL = 1200; // 20 minutes
    const LIB_VERSION = '1.x-dev';

    /** @var Client */
    private $httpClient;

    /** @var string */
    private $userAgent;

    /** @var CacheInterface */
    private $cache;

    /** @var string */
    private $version;

    public function __construct(Client $httpClient = null)
    {
        $this->httpClient = $httpClient ?: new Client();
    }

    /**
     * @param string $path
     * @param array $parameters
     * @return array
     */
    public function request($path, array $parameters = [])
    {
        $uri = $this->buildUri($path);
        $parameters = $this->encodeParameters($parameters);
        $this->prepareHttpClient();
        $response = $this
            ->httpClient
            ->request('GET', $uri, [
                'query' => $parameters,
                'headers' => [
                    'Accept' => 'application/json',
                ],
            ])
        ;

        return GuzzleHttp\json_decode($response->getBody(), true);
    }

    /**
     * @param string $path
     * @return bool
     */
    public function exists($path)
    {
        try {
            $uri = $this->buildUri($path);
            $this->prepareHttpClient();

            return 200 === $this
                ->httpClient
                ->request('HEAD', $uri)
                ->getStatusCode()
            ;
        } catch (ClientException $exception) {
            if ($exception->hasResponse() && 404 === $exception->getResponse()->getStatusCode()) {
                return false;
            }
            throw $exception;
        }
    }

    /**
     * @param string $userAgent
     */
    public function setUserAgent($userAgent)
    {
        $this->userAgent = $userAgent;
    }

    public function setCache(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @param string $version
     */
    public function setVersion($version)
    {
        $this->version = $version;
    }

    /**
     * @param string $path
     * @return string
     */
    private function buildUri($path)
    {
        // Prepends version to the path when it's available and path is relative
        if ($this->version && '/' !== mb_substr($path, 0, 1)) {
            $path = $this->version . '/' . $path;
        }

        return self::BASE_URI . '/' . ltrim($path, '/');
    }

    /**
     * @param array $parameters
     * @return array
     * @see https://github.com/CrossRef/rest-api-doc#multiple-filters
     * @see https://github.com/CrossRef/rest-api-doc#facet-counts
     */
    private function encodeParameters(array $parameters)
    {
        $encodable = ['filter', 'facet'];
        foreach ($encodable as $key) {
            if (!isset($parameters[$key]) || !is_array($parameters[$key])) {
                continue;
            }
            $encoded = [];
            foreach ($parameters[$key] as $name => $value) {
                if (!is_array($value)) {
                    $value = [$value];
                }
                foreach ($value as $actual) {
                    if (is_bool($actual)) {
                        $actual = $actual ? 'true' : 'false';
                    }
                    $encoded[] = $name . ':' . $actual;
                }
            }
            $parameters[$key] = implode(',', $encoded);
        }

        return $parameters;
    }

    private function prepareHttpClient()
    {
        $handler = $this->httpClient->getConfig('handler');

        // Prepends middleware that injects User-Agent header
        $userAgentName = __CLASS__ . '_user_agent';
        $handler->remove($userAgentName);
        $handler->unshift(Middleware::mapRequest(function (Request $request) {
            return $request->withHeader('User-Agent', implode(' ', array_filter([
                $this->userAgent,
                sprintf('RenanBr-CrossRef-Client/%s', self::LIB_VERSION),
                GuzzleHttp\default_user_agent()
            ])));
        }), $userAgentName);

        // Appends cache middleware
        $cacheName = __CLASS__ . '_cache';
        $handler->remove($cacheName);
        $this->cache && $handler->push(new CacheMiddleware(
            new GreedyCacheStrategy(
                new Psr16CacheStorage($this->cache),
                self::CACHE_TTL
            )
        ), $cacheName);
    }
}
