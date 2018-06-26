<?php

/*
 * This file is part of CrossRef Client.
 *
 * (c) Renan de Lima Barbosa <renandelima@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RenanBr\CrossRefClient;

use Cache\Adapter\PHPArray\ArrayCachePool;
use Cache\Bridge\SimpleCache\SimpleCacheBridge;
use Concat\Http\Middleware\RateLimitProvider as RateLimitProviderInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\SimpleCache\CacheInterface;

/**
 * @see https://github.com/CrossRef/rest-api-doc#rate-limits
 */
class RateLimitProvider implements RateLimitProviderInterface
{
    const TIMESTAMPS_INDEX = 'crossref-client-rate-limit-timestamps';
    const INTERVAL_INDEX = 'crossref-client-rate-limit-interval';
    const LIMIT_INDEX = 'crossref-client-rate-limit-limit';

    const DEFAULT_LIMIT = 50;
    const DEFAULT_INTERVAL = 1;

    /** @var CacheInterface */
    private $cache;

    public function __construct(CacheInterface $cache = null)
    {
        $this->setCache($cache ?: new SimpleCacheBridge(new ArrayCachePool()));
    }

    public function setCache(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    public function getLastRequestTime()
    {
        $timestamps = $this->getTimestamps();

        return end($timestamps) ?: null;
    }

    public function setLastRequestTime()
    {
        // Build list with timestamps for the current interval
        $interval = $this->getRateInterval();
        $timestamps = $this->getTimestamps($interval);

        // Append current time to the list
        $now = microtime(true);
        $timestamps[] = $now;

        // Keep list in cache until the rate limit will be reset
        $this->cache->set(self::TIMESTAMPS_INDEX, $timestamps, $interval);
    }

    public function getRequestTime(RequestInterface $request)
    {
        return microtime(true);
    }

    /**
     * Returns the amount of time that is required to have passed since the last
     * request was made.
     *
     * The delay expected (what this method must return) is based on to the last
     * request. The rate limit defined by the API is based on limited requests
     * in a time interval. It calculates the delay based on the time we should
     * wait to make any request from now.
     */
    public function getRequestAllowance(RequestInterface $request)
    {
        $interval = $this->getRateInterval();
        $timestamps = $this->getTimestamps($interval);
        if (count($timestamps) < $this->getRateLimit()) {
            return 0;
        }

        return (reset($timestamps) + $this->getRateInterval()) - end($timestamps);
    }

    public function setRequestAllowance(ResponseInterface $response)
    {
        if (!$response->hasHeader('X-Rate-Limit-Interval') || !$response->hasHeader('X-Rate-Limit-Limit')) {
            return;
        }

        $interval = (int) $response->getHeaderLine('X-Rate-Limit-Interval');
        $limit = (int) $response->getHeaderLine('X-Rate-Limit-Limit');

        // Keep rates in the cache for a while
        $ttl = $interval * 10;

        $this->cache->set(self::INTERVAL_INDEX, $interval, $ttl);
        $this->cache->set(self::LIMIT_INDEX, $limit, $ttl);
    }

    /**
     * @return int
     */
    private function getRateInterval()
    {
        return $this->cache->get(self::INTERVAL_INDEX, self::DEFAULT_INTERVAL);
    }

    /**
     * @return int
     */
    private function getRateLimit()
    {
        return $this->cache->get(self::LIMIT_INDEX, self::DEFAULT_LIMIT);
    }

    /**
     * @param  int   $sinceInSeconds
     * @return array
     */
    private function getTimestamps($sinceInSeconds = null)
    {
        $timestamps = (array) $this->cache->get(self::TIMESTAMPS_INDEX);
        if (null !== $sinceInSeconds) {
            $cut = strtotime("-$sinceInSeconds seconds");
            $timestamps = array_filter($timestamps, function ($time) use ($cut) {
                return $time >= $cut;
            });
        }

        return $timestamps;
    }
}
