<?php

$config = PhpCsFixer\Config::create();
$config->setRules([
    '@PSR2' => true,
]);

$finder = PhpCsFixer\Finder::create();

/*
 * You can set manually these paths:
 */
$finder->in([
    'bin',
    'src',
    'tests'
]);

$config->setFinder($finder);

return $config;
