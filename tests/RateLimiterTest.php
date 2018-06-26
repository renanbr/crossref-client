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

class RateLimiterTest extends TestCase
{
    public function testDelayIsTriggered()
    {
        // Prepare mocked responses that inform to the client that the max
        // request per second is 2
        $headers = [
            'X-Rate-Limit-Interval' => '1s',
            'X-Rate-Limit-Limit' => '2',
        ];
        $responses = [
            new Response(200, $headers, 'null'),
            new Response(200, $headers, 'null'),
            new Response(200, $headers, 'null'),
        ];
        $client = $this->buildMockedCrossRefClient($responses);

        $start = microtime(true);

        // First request must be executed instantaneously
        $client->request('foo');
        $this->assertLessThan(1, microtime(true) - $start);

        // Second request too
        $client->request('foo');
        $this->assertLessThan(1, microtime(true) - $start);

        // Third request has to wait to be executed
        $client->request('foo');
        $this->assertGreaterThanOrEqual(1, microtime(true) - $start);
    }
}
