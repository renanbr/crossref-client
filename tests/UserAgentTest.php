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

class UserAgentTest extends TestCase
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

        $client->setUserAgent('GroovyBib/1.1');
        $client->request('foo');

        $userAgent = $transactions[0]['request']->getHeaderLine('User-Agent');
        $this->assertStringStartsWith('GroovyBib/1.1', $userAgent);
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

        $client->setUserAgent('FunkyLib/1.4');
        $client->exists('bar');

        $userAgent = $transactions[0]['request']->getHeaderLine('User-Agent');
        $this->assertStringStartsWith('FunkyLib/1.4', $userAgent);
    }
}
