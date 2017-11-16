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

class RequestOperationTest extends TestCase
{
    public function testResponseDecoding()
    {
        $client = $this->buildClient(
            HandlerStack::create(
                new MockHandler([
                    new Response(200, [], '"bar"'),
                ])
            )
        );

        $this->assertSame('bar', $client->request('foo'));
    }

    public function testTargetedEndpoint()
    {
        $handlerStack = HandlerStack::create(
            new MockHandler([
                new Response(200, [], 'null'),
            ])
        );
        $transactions = [];
        $handlerStack->push(Middleware::history($transactions));
        $client = $this->buildClient($handlerStack);

        $client->request('foo/bar');

        $request = $transactions[0]['request'];
        $this->assertSame('GET', $request->getMethod());
        $this->assertSame('https://api.crossref.org/foo/bar', (string) $request->getUri());
    }

    public function testTargetedUrlWithParameters()
    {
        $handlerStack = HandlerStack::create(
            new MockHandler([
                new Response(200, [], 'null'),
            ])
        );
        $transactions = [];
        $handlerStack->push(Middleware::history($transactions));
        $client = $this->buildClient($handlerStack);

        $client->request('with/parameters', [
            'foo' => 'bar',
        ]);

        $request = $transactions[0]['request'];
        $this->assertSame(
            'https://api.crossref.org/with/parameters?foo=bar',
            (string) $request->getUri()
        );
    }
}
