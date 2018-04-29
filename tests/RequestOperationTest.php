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

use GuzzleHttp\Psr7\Response;

class RequestOperationTest extends TestCase
{
    public function testResponseDecoding()
    {
        $responses = [new Response(200, [], '"bar"')];
        $client = $this->buildMockedCrossRefClient($responses);

        $this->assertSame('bar', $client->request('foo'));
    }

    public function testTargetedEndpoint()
    {
        $responses = [new Response(200, [], 'null')];
        $transactions = [];
        $client = $this->buildMockedCrossRefClient($responses, $transactions);

        $client->request('foo/bar');

        $request = $transactions[0]['request'];
        $this->assertSame('GET', $request->getMethod());
        $this->assertSame('https://api.crossref.org/foo/bar', (string) $request->getUri());
    }

    public function testTargetedUrlWithParameters()
    {
        $responses = [new Response(200, [], 'null')];
        $transactions = [];
        $client = $this->buildMockedCrossRefClient($responses, $transactions);

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
