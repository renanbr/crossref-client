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

use GuzzleHttp\HandlerStack;
use PHPUnit\Framework\TestCase as BaseTestCase;
use RenanBr\CrossRefClient;

class TestCase extends BaseTestCase
{
    /**
     * @return CrossRefClient
     */
    protected function buildClient(HandlerStack $handler)
    {
        $client = $this
            ->getMockBuilder(CrossRefClient::class)
            ->setMethods(['createGuzzleHandlerStack'])
            ->getMock()
        ;
        $client
            ->method('createGuzzleHandlerStack')
            ->willReturn($handler)
        ;

        return $client;
    }
}
