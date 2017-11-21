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

class ParametersEncodingTest extends TestCase
{
    public function testFilterEncoding()
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
            'filter' => [
                'foobar' => 'barfoo',
                'foo' => true,
                'bar' => false,
            ],
        ]);

        $request = $transactions[0]['request'];
        parse_str($request->getUri()->getQuery(), $query);
        $this->assertSame(
            ['filter' => 'foobar:barfoo,foo:true,bar:false'],
            $query
        );
    }

    public function testFacetEncoding()
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
            'facet' => [
                'foo' => 'oof',
                'bar' => 'rab',
            ],
        ]);

        $request = $transactions[0]['request'];
        parse_str($request->getUri()->getQuery(), $query);
        $this->assertSame(
            ['facet' => 'foo:oof,bar:rab'],
            $query
        );
    }

    public function testEncodedParametersMustNotBeTouched()
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
            'filter' => 'foo',
            'facet' => 'bar',
        ]);

        $request = $transactions[0]['request'];
        $this->assertSame(
            'filter=foo&facet=bar',
            $request->getUri()->getQuery()
        );
    }

    public function testArrayValueEncoding()
    {
        $handlerStack = HandlerStack::create(
            new MockHandler([
                new Response(200, [], 'null'),
            ])
        );
        $transactions = [];
        $handlerStack->push(Middleware::history($transactions));
        $client = $this->buildClient($handlerStack);

        $client->request('with/array/values', [
            'filter' => [
                'doi' => [
                    '10.5555/12345678',
                    '10.5555/777766665555',
                ],
            ],
        ]);

        $request = $transactions[0]['request'];
        parse_str($request->getUri()->getQuery(), $query);
        $this->assertSame(
            ['filter' => 'doi:10.5555/12345678,doi:10.5555/777766665555'],
            $query
        );
    }
}
