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
    const CACHE_TTL = 600; // 10 minutes
    const LIB_VERSION = '1.x-dev';

    /** @var string */
    private $userAgent;

    /** @var CacheInterface */
    private $cache;

    /** @var string */
    private $version;

    /**
     * @param string $path
     * @param array $parameters
     * @return array
     */
    public function request($path, array $parameters = [])
    {
        $path = $this->buildPath($path);
        $parameters = $this->encodeParameters($parameters);
        $response = $this
            ->buildGuzzleClient()
            ->request('GET', $path, [
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
            return 200 === $this
                ->buildGuzzleClient()
                ->request('HEAD', $this->buildPath($path))
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
    private function buildPath($path)
    {
        return $this->version && '/' !== mb_substr($path, 0, 1)
            ? implode('/', [$this->version, $path])
            : $path;
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

    /**
     * @return Client
     */
    private function buildGuzzleClient()
    {
        $handlerStack = $this->createGuzzleHandlerStack();

        // Specifies User-Agent header
        $handlerStack->unshift(
            Middleware::mapRequest(function (Request $request) {
                return $request->withHeader('User-Agent', implode(' ', array_filter([
                    $this->userAgent,
                    sprintf('RenanBr-CrossRef-Client/%s', self::LIB_VERSION),
                    GuzzleHttp\default_user_agent()
                ])));
            })
        );

        // Injects cache middleware if storage is available
        $this->cache && $handlerStack->push(
            new CacheMiddleware(
                new GreedyCacheStrategy(
                    new Psr16CacheStorage($this->cache),
                    self::CACHE_TTL
                )
            )
        );

        return new Client([
            'base_uri' => self::BASE_URI,
            'handler' => $handlerStack,
        ]);
    }

    /**
     * @return HandlerStack
     */
    protected function createGuzzleHandlerStack()
    {
        return HandlerStack::create();
    }
}
