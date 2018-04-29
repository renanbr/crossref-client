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

class UserAgentTest extends TestCase
{
    public function testRequestOperation()
    {
        $responses = [new Response(200, [], 'null')];
        $transactions = [];
        $client = $this->buildMockedCrossRefClient($responses, $transactions);

        $client->setUserAgent('GroovyBib/1.1');
        $client->request('foo');

        $userAgent = $transactions[0]['request']->getHeaderLine('User-Agent');
        $this->assertStringStartsWith('GroovyBib/1.1', $userAgent);
    }

    public function testExistsOperation()
    {
        $responses = [new Response(200)];
        $transactions = [];
        $client = $this->buildMockedCrossRefClient($responses, $transactions);

        $client->setUserAgent('FunkyLib/1.4');
        $client->exists('bar');

        $userAgent = $transactions[0]['request']->getHeaderLine('User-Agent');
        $this->assertStringStartsWith('FunkyLib/1.4', $userAgent);
    }
}
