<?php

/*
 * This file is part of CrossRef Client.
 *
 * (c) Renan de Lima Barbosa <renandelima@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RenanBr;

use GuzzleHttp;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\HandlerStack;

class CrossRefClient
{
    const BASE_URI = 'https://api.crossref.org';

    /**
     * @param string $path
     * @param array $parameters
     * @return array
     */
    public function request($path, array $parameters = [])
    {
        $response = $this
            ->buildGuzzleClient()
            ->request('GET', $path, [
                'query' => $parameters,
                'headers' => [
                    'Accept' => 'application/json',
                ],
            ])
        ;

        return GuzzleHttp\json_decode($response->getBody(), true);
    }

    /**
     * @param string $path
     * @return bool
     */
    public function exists($path)
    {
        try {
            return 200 === $this
                ->buildGuzzleClient()
                ->request('HEAD', $path)
                ->getStatusCode()
            ;
        } catch (ClientException $exception) {
            if ($exception->hasResponse() && 404 === $exception->getResponse()->getStatusCode()) {
                return false;
            }
            throw $exception;
        }
    }

    /**
     * @return Client
     */
    private function buildGuzzleClient()
    {
        $handlerStack = $this->createGuzzleHandlerStack();

        return new Client([
            'base_uri' => self::BASE_URI,
            'handler' => $handlerStack,
        ]);
    }

    /**
     * @return HandlerStack
     */
    protected function createGuzzleHandlerStack()
    {
        return HandlerStack::create();
    }
}
