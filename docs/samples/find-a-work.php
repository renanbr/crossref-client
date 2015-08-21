<?php

require __DIR__ . '/../../vendor/autoload.php';

$client = new RenanBr\CrossRefClient();
    // or new RenanBr\CrossRefClient('works')
    // accepted values: works, funders, members, types, licenses, journals

$doi = '10.1016/j.sbspro.2014.12.017';
$work = $client->find($doi);
echo
    'DOI ' . $doi . '<br>' .
    '<a href="' . $work->URL . '">' . $work->title[0] . '</a><br>' .
    'by ' . $work->author[0]->family . '<br>' .
    'in ' . $work->publisher;
