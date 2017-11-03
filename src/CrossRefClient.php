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

use RenanBr\CrossRefClient\Caller;
use RenanBr\CrossRefClient\Rows;

class CrossRefClient
{
    /** @var string */
    private $resource;

    /** @var Caller */
    private $caller;

    /**
     * @param string $resource
     * @param Caller $caller
     */
    public function __construct($resource = null, Caller $caller = null)
    {
        $this->resource = $resource ?: 'works';
        $this->caller = $caller ?: new Caller();
    }

    /**
     * @param string $value
     * @return \stdClass
     */
    public function find($value)
    {
        $path = $this->resource . '/' . $value;

        return $this->caller->request($path, [])->message;
    }

    /**
     * @param null|string $query
     * @param null|array $filters
     * @param null|array $parameters
     * @return Rows
     */
    public function search($query = null, array $filters = null, array $parameters = null)
    {
        if ($filters) {
            $filters = $this->encodeFilters($filters);
        }
        $parameters = array_filter(array_merge(
            $parameters,
            ['query' => $query, 'filter' => $filters]
        ));
        return new Rows($this->resource, $parameters, $this->caller);
    }

    /**
     * @param array $filters
     * @return string
     */
    private function encodeFilters(array $filters)
    {
        $encoded = [];
        foreach ($filters as $name => $value) {
            if (is_bool($value)) {
                $value = $value ? 'true' : 'false';
            }
            $encoded[] = $name . ':' . $value;
        }
        return implode(',', $encoded);
    }
}
