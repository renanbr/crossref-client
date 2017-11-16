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

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;

class ExistsOperationTest extends TestCase
{
    public function testPositiveResponseReading()
    {
        $client = $this->buildClient(
            HandlerStack::create(
                new MockHandler([
                    new Response(200),
                ])
            )
        );

        $this->assertTrue($client->exists('it/exists'));
    }

    public function testNegativeResponseReading()
    {
        $client = $this->buildClient(
            HandlerStack::create(
                new MockHandler([
                    new Response(404),
                ])
            )
        );

        $this->assertFalse($client->exists('not/found'));
    }

    public function testTargetedEndpoint()
    {
        $handlerStack = HandlerStack::create(
            new MockHandler([
                new Response(200),
            ])
        );
        $transactions = [];
        $handlerStack->push(Middleware::history($transactions));
        $client = $this->buildClient($handlerStack);

        $client->exists('foo/bar');

        $request = $transactions[0]['request'];
        $this->assertSame('HEAD', $request->getMethod());
        $this->assertSame('https://api.crossref.org/foo/bar', (string) $request->getUri());
    }
}
