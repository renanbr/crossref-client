<h1 align="center">PHP CrossRef Client</h1>
<p align="center">
    This is a library for the
    <a href="https://www.crossref.org/services/metadata-delivery/rest-api/">Crossref REST API</a>
    written in <a href="https://php.net">PHP</a>.
</p>
<p align="center">
    <a href="https://www.crossref.org/services/metadata-delivery/rest-api/">
        <img src="https://assets.crossref.org/logo/crossref-metadata-apis-200.svg" width="200" height="83" alt="Crossref Metadata APIs logo">
    </a>
    <a href="https://php.net">
        <img src="https://upload.wikimedia.org/wikipedia/commons/2/27/PHP-logo.svg" height="83" alt="PHP logo">
    </a>
</p>

[![Build Status](https://travis-ci.org/renanbr/crossref-client.svg?branch=master)](https://travis-ci.org/renanbr/crossref-client)

## Table of contents

* [Introduction](#introduction)
* [Installing](#installing)
* [Usage](#usage)
   * [Singletons](#singletons)
   * [Determine existence of a singleton](#determine-existence-of-a-singleton)
   * [Lists](#lists)
* [Configuration](#configuration)
   * [Caching results](#caching-results)
   * [Identifying your script](#identifying-your-script)
   * [Tying to a specific major version](#tying-to-a-specific-major-version)
* [Handling errors](#handling-errors)

## Introduction

This is **NOT** an official library! The intent of this library is to provide an easy way to make requests to the CrossRef's REST API. You **SHOULD** read this documentation in conjunction with the [official documentation](https://github.com/CrossRef/rest-api-doc).

Highlighted features:

- You don't need to worry about making HTTP requests;
- Proper [exceptions] are thrown if a HTTP error occurs;
- You receive responses as-is, with no treatment;
- [Filter](https://github.com/CrossRef/rest-api-doc#filter-names) and [facet](https://github.com/CrossRef/rest-api-doc#facet-counts) parameters will be encoded if needed;
- You can [be polite](https://github.com/CrossRef/rest-api-doc#etiquette) (caching, identifying your script);
- You can [tie to a specific major version of the API](https://github.com/CrossRef/rest-api-doc#how-to-manage-api-versions).

Library's summary:

```php
class RenanBr\CrossRefClient
{
    // Returns JSON decoded as array
    public function request($path, array $parameters = []);

    // Returns boolean
    public function exists($path);

    public function setUserAgent($userAgent);
    public function setCache(Psr\SimpleCache\CacheInterface $cache);
    public function setVersion($version);
}
```

## Installing

```bash
composer require renanbr/crossref-client ^1@dev
```

## Usage

### Singletons

> Singletons are single results. Retrieving metadata for a specific identifier (e.g. DOI, ISSN, funder_identifier) typically returns in a singleton result.

See: https://github.com/CrossRef/rest-api-doc#singletons

```php
$client = new RenanBr\CrossRefClient();
$work = $client->request('works/10.1037/0003-066X.59.1.29');
print_r($work);
```

The above example will output:

```
Array
(
    [status] => ok
    [message-type] => work
    [message-version] => 1.0.0
    [message] => Array
        (
            ...

            [DOI] => 10.1037/0003-066x.59.1.29
            [type] => journal-article

            ...

            [title] => Array
                (
                    [0] => How the Mind Hurts and Heals the Body.
                )

            ...
        )
)
```

### Determine existence of a singleton

> (...) [You can] determine "existence" of a singleton. The advantage of this technique is that it is very fast because it does not return any metadata (...)

See: https://github.com/CrossRef/rest-api-doc#headers-only

```php
$client = new RenanBr\CrossRefClient();
$exists = $client->exists('members/98');
var_dump(exists);
```

The above example will output:

```
bool(true)
```

### Lists

> Lists results can contain multiple entries. Searching or filtering typically returns a list result.

A list has two parts: Summary; and Items. Normally, an API list result will return both.

See: https://github.com/CrossRef/rest-api-doc#lists

```php
$client = new RenanBr\CrossRefClient();

$parameters = [
    'query' => 'global state',
    'filter' => [
        'has-orcid' => true,
    ],
];
$result = $client->request('works', $parameters);

foreach ($result['message']['items'] as $work) {
    // ...
}
```

## Configuration

### Caching results

> Cache data so you don't request the same data over and over again.

See: https://github.com/CrossRef/rest-api-doc#etiquette

```php
$client = new RenanBr\CrossRefClient();
$client->setCache(new voku\cache\CachePsr16());

// ...
```

The above example uses [voku/simple-cache](https://github.com/voku/simple-cache) as cache implementation, but you can use [any PSR-16 implementation](https://packagist.org/providers/psr/simple-cache-implementation) because `setCache()` accept [Psr\SimpleCache\CacheInterface](http://www.php-fig.org/psr/psr-16/#21-cacheinterface) as argument.

### Identifying your script

> As of September 18th 2017 any API queries that use HTTPS and have appropriate contact information will be directed to a special pool of API machines that are reserved for polite users.

See: https://github.com/CrossRef/rest-api-doc#good-manners--more-reliable-service

```php
$client = new RenanBr\CrossRefClient();
$client->setUserAgent('GroovyBib/1.1 (https://example.org/GroovyBib/; mailto:GroovyBib@example.org)');

// ...
```

The above example makes all subsequent requests attach the contact information given.

### Tying to a specific major version

> If you need to tie your implementation to a specific major version of the API, you can do so by using version-specific routes. The default route redirects to the most recent version of the API.

See: https://github.com/CrossRef/rest-api-doc#how-to-manage-api-versions

```php
$client = new RenanBr\CrossRefClient();
$client->setVersion('v55');

// ...
```

The above example tie all subsequent requests to the API version `v55`.

## Handling errors

As this library uses [guzzlehttp/guzzle](http://guzzlephp.org) internally. Please refer to the [Guzzle Exceptions documentation](http://docs.guzzlephp.org/en/stable/quickstart.html#exceptions) to see how to handle [exceptions] properly.

[exceptions]: http://php.net/exception
