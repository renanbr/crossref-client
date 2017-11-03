<?php

/*
 * This file is part of CrossRef Client.
 *
 * (c) Renan de Lima Barbosa <renandelima@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RenanBr\CrossRefClient;

class Caller
{
    /** @var string */
    private $baseUrl = 'http://api.crossref.org';

    /**
     * @param string $path
     * @param array $parameters
     * @return \stdClass
     */
    public function request($path, array $parameters)
    {
        $url = $this->baseUrl . '/' . $path . '?' . http_build_query($parameters);
        $json = file_get_contents($url);
        $object = json_decode($json);

        if (!is_object($object)) {
            throw new \Exception('Bad response.');
        }

        if ('failed' === $object->status) {
            throw new \Exception($object->message->message);
        }

        return $object;
    }
}
