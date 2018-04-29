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

class TiedVersionRequestTest extends TestCase
{
    public function testRequestOperation()
    {
        $responses = [new Response(200, [], 'null')];
        $transactions = [];
        $client = $this->buildMockedCrossRefClient($responses, $transactions);

        $client->setVersion('v10');
        $client->request('foo');

        $request = $transactions[0]['request'];
        $this->assertSame('/v10/foo', $request->getUri()->getPath());
    }

    public function testExistsOperation()
    {
        $responses = [new Response(200)];
        $transactions = [];
        $client = $this->buildMockedCrossRefClient($responses, $transactions);

        $client->setVersion('v50');
        $client->exists('bar');

        $request = $transactions[0]['request'];
        $this->assertSame('/v50/bar', $request->getUri()->getPath());
    }
}
