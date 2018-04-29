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

class ExistsOperationTest extends TestCase
{
    public function testPositiveResponseReading()
    {
        $responses = [new Response(200)];
        $client = $this->buildMockedCrossRefClient($responses);

        $this->assertTrue($client->exists('it/exists'));
    }

    public function testNegativeResponseReading()
    {
        $responses = [new Response(404)];
        $client = $this->buildMockedCrossRefClient($responses);

        $this->assertFalse($client->exists('not/found'));
    }

    public function testTargetedEndpoint()
    {
        $responses = [new Response(200)];
        $transactions = [];
        $client = $this->buildMockedCrossRefClient($responses, $transactions);

        $client->exists('foo/bar');

        $request = $transactions[0]['request'];
        $this->assertSame('HEAD', $request->getMethod());
        $this->assertSame('https://api.crossref.org/foo/bar', (string) $request->getUri());
    }
}
