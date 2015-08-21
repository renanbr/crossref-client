<?php

require __DIR__ . '/../../vendor/autoload.php';

$client = new RenanBr\CrossRefClient();

$query = 'feminism';
$filters = ['has-license' => true];
$parameters = ['sort' => 'published', 'order' => 'desc'];

$works = $client->search($query, $filters, $parameters);

echo 'Total ' . count($works);
echo '<ol>';
foreach ($works as $i => $work) {
    echo
        '<li>' . $i . ' ' .
            '<a href="' . $work->URL . '">' . $work->title[0] . '</a> ' .
            'by ' . $work->author[0]->family .
        '</li>';
}
echo '</ol>';
