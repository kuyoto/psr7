<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__ . DIRECTORY_SEPARATOR . 'src')
    ->in(__DIR__ . DIRECTORY_SEPARATOR . 'tests')
    ->notPath('Fixture');


return PhpCsFixer\Config::create()
    ->setUsingCache(true)
    ->setRiskyAllowed(true)
    ->setRules([
        '@PhpCsFixer' => true,
        'yoda_style' => false,
    ])
    ->setFinder($finder);
