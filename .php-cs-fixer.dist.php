<?php

return (new PhpCsFixer\Config())
    ->setCacheFile(__DIR__ . '/var/.php_cs.cache')
    ->setRiskyAllowed(true)
    ->setRules([
        '@PER-CS' => true,
        '@PER-CS:risky' => true,
        'visibility_required' => ['elements' => ['method', 'property']], // PHP 5 compatibility
    ])
    ->setFinder(PhpCsFixer\Finder::create()->in([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ]));
