<?php

/*
 * This file is part of CrossRef Client.
 *
 * (c) Renan de Lima Barbosa <renandelima@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RenanBr\CrossRefClient\Test;

use Cache\Adapter\PHPArray\ArrayCachePool;
use Cache\Bridge\SimpleCache\SimpleCacheBridge;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

class CachingTest extends TestCase
{
    public function testRequest()
    {
        $firstClient = $this->buildClient(
            HandlerStack::create(
                new MockHandler([
                    new Response(200, [], '"first"'),
                    new Response(200, [], '"first again"'),
                ])
            )
        );

        $secondClient = $this->buildClient(
            HandlerStack::create(
                new MockHandler([
                    new Response(200, [], '"second"'),
                    new Response(200, [], '"second again"'),
                ])
            )
        );

        $thirdClient = $this->buildClient(
            HandlerStack::create(
                new MockHandler([
                    new Response(200, [], '"third"'),
                    new Response(200, [], '"third again"'),
                ])
            )
        );

        $cache = new SimpleCacheBridge(new ArrayCachePool());
        $firstClient->setCache($cache);
        // $secondClient has no cache intentionally
        $thirdClient->setCache($cache);

        $this->assertSame('first', $firstClient->request('foo'));
        $this->assertSame('first', $firstClient->request('foo'));
        $this->assertSame('second', $secondClient->request('foo'));
        $this->assertSame('second again', $secondClient->request('foo'));
        $this->assertSame('first', $thirdClient->request('foo'));
        $this->assertSame('first', $thirdClient->request('foo'));
    }
}
