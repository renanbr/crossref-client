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

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use PHPUnit\Framework\TestCase as BaseTestCase;
use RenanBr\CrossRefClient;

class TestCase extends BaseTestCase
{
    /**
     * @return CrossRefClient
     */
    protected function buildMockedCrossRefClient(array $responses, array &$transactionsContainer = null)
    {
        $handler = HandlerStack::create(new MockHandler($responses));
        if (null !== $transactionsContainer) {
            $handler->push(Middleware::history($transactionsContainer));
        }

        return new CrossRefClient(new Client([
            'handler' => $handler,
        ]));
    }
}
