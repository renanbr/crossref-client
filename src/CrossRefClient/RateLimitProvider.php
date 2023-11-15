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
 * @internal
 */
class RateLimitProvider implements RateLimitProviderInterface
{
    const CACHE_INDEX = 'crossref-client-rate-limit';

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

    public function getLastRequestTime(RequestInterface $request)
    {
        $constraint = $this->getConstraint();

        return end($constraint['requests']) ?: null;
    }

    public function setLastRequestTime(RequestInterface $request)
    {
        $constraint = $this->getConstraint();
        $constraint['requests'][] = microtime(true);
        $this->setConstraint($constraint);
    }

    public function getRequestTime(RequestInterface $request)
    {
        return microtime(true);
    }

    public function getRequestAllowance(RequestInterface $request)
    {
        $constraint = $this->getConstraint();
        if (!$constraint['interval'] || !$constraint['limit'] || \count($constraint['requests']) < $constraint['limit']) {
            return 0;
        }

        // Returns the amount of time that is required to have passed since the
        // last request was made
        return reset($constraint['requests']) + $constraint['interval'] - end($constraint['requests']);
    }

    public function setRequestAllowance(ResponseInterface $response)
    {
        $constraint = $this->getConstraint();
        $constraint['interval'] = (int) $response->getHeaderLine('X-Rate-Limit-Interval');
        $constraint['limit'] = (int) $response->getHeaderLine('X-Rate-Limit-Limit');
        $this->setConstraint($constraint);
    }

    /**
     * @return array
     */
    private function getConstraint()
    {
        return $this->cache->get(self::CACHE_INDEX, [
            'interval' => null,
            'limit' => null,
            'requests' => [],
        ]);
    }

    private function setConstraint(array $constraint)
    {
        if ($constraint['interval']) {
            $cut = microtime(true) - $constraint['interval'];
            $constraint['requests'] = array_filter($constraint['requests'], static function ($time) use ($cut) {
                return $time >= $cut;
            });
        }

        $ttl = $constraint['interval'] ?: 60;
        $this->cache->set(self::CACHE_INDEX, $constraint, $ttl);
    }
}
