# CrossRef Client

This project provides a PHP client library for [CrossRef API](http://api.crossref.org).

## Usage

### Find

```php
<?php

$client = new RenanBr\CrossRefClient();
    // or new RenanBr\CrossRefClient('works')
    // accepted values: works, funders, members, types, licenses, journals

$work = $client->find('10.1016/j.sbspro.2014.12.017');
$work->title[0]; // Managing Resources against Gender-based Violence. An Intervention from Feminism and Transversality, through a Case Study of the Simone de Beauvoir Association of León.
```

### Search

```php
<?php

$client = new RenanBr\CrossRefClient();

$query = 'feminism';
$filters = ['has-license' => true];
$parameters = ['sort' => 'published', 'order' => 'desc'];

$works = $client->search($query, $filters, $parameters);

count($works);
foreach ($works as $work) {
    $work->title[0];
}
```

See [CrossRef API documentation](http://api.crossref.org) for more information about filters and parameters.

## Install

`composer require renanbr/crossref-client`
