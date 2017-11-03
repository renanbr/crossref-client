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

class Rows implements \Iterator, \Countable
{
    /** @var string */
    private $resource;

    /** @var array */
    private $parameters;

    /** @var int */
    private $page;

    /** @var Caller */
    private $caller;

    /** @var \stdClass */
    private $response;

    /** @var \ArrayIterator */
    private $items;

    /**
     * @param string $resource
     * @param array $parameters
     * @param Caller $caller
     */
    public function __construct($resource, array $parameters, Caller $caller)
    {
        $this->resource = $resource;
        $this->parameters = $parameters;
        $this->page = 0;
        $this->caller = $caller;
        $this->load();
    }

    public function current()
    {
        return $this->items->current();
    }

    public function key()
    {
        $itemsPerPage = $this->response->message->{'items-per-page'};
        return ($this->page * $itemsPerPage) + $this->items->key();
    }

    public function next()
    {
        $this->items->next();
        if (!$this->items->valid()) {
            $this->page++;
            $this->load();
        }
    }

    public function rewind()
    {
        if (0 !== $this->page) {
            $this->page = 0;
            $this->load();
        }
    }

    public function valid()
    {
        return $this->key() < $this->count();
    }

    public function count()
    {
        return $this->response->message->{'total-results'};
    }

    private function load()
    {
        if ($this->response) {
            // avoids unnecessary request to the following page after the last one
            $itemsPerPage = $this->response->message->{'items-per-page'};
            $lastPage = ceil(($this->count() / $itemsPerPage) - 1);
            if ($this->page > $lastPage) {
                $this->items = new \ArrayIterator([]);
                return;
            }
            $offset = $this->page * $itemsPerPage;
        } else {
            $offset = 0;
        }

        $this->parameters['offset'] = $offset;
        $this->response = $this->caller->request($this->resource, $this->parameters);
        $this->items = new \ArrayIterator($this->response->message->items);
    }
}
