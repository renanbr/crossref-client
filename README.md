# CrossRef Client

This is a client for [CrossRef API](http://api.crossref.org) written in PHP.

# Installing

```bash
composer require renanbr/crossref-client
```

# Usage

## Finding a resource

```php
use RenanBr\CrossRefClient;

$client = new CrossRefClient();
    // or new CrossRefClient('works')
    // works (default), funders, members, types, licenses, journals

$work = $client->find('10.1016/j.sbspro.2014.12.017');

print_r($work);
```

This will output

```
Managing Resources against Gender-based Violence. An Intervention from Feminism and Transversality, through a Case Study of the Simone de Beauvoir Association of León.
```

## Searching

```php
use RenanBr\CrossRefClient;

$client = new RenanBr\CrossRefClient();

$query = 'feminism';
$filters = ['has-license' => true];
$parameters = ['sort' => 'published', 'order' => 'desc'];

$works = $client->search($query, $filters, $parameters);

foreach ($works as $work) {
    print_r($work->title[0]);
}
```

See [CrossRef API documentation](http://api.crossref.org) for more information about filters and parameters.
