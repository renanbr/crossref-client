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

class TiedVersionRequestTest extends TestCase
{
    public function testRequestOperation()
    {
        $handlerStack = HandlerStack::create(
            new MockHandler([
                new Response(200, [], 'null'),
            ])
        );
        $transactions = [];
        $handlerStack->push(Middleware::history($transactions));
        $client = $this->buildClient($handlerStack);

        $client->setVersion('v10');
        $client->request('foo');

        $request = $transactions[0]['request'];
        $this->assertSame('/v10/foo', $request->getUri()->getPath());
    }

    public function testExistsOperation()
    {
        $handlerStack = HandlerStack::create(
            new MockHandler([
                new Response(200),
            ])
        );
        $transactions = [];
        $handlerStack->push(Middleware::history($transactions));
        $client = $this->buildClient($handlerStack);

        $client->setVersion('v50');
        $client->exists('bar');

        $request = $transactions[0]['request'];
        $this->assertSame('/v50/bar', $request->getUri()->getPath());
    }
}
