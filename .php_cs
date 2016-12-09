<?php

$finder = PhpCsFixer\Finder::create()
        ->in(__DIR__ . '/src/')
;
 
 return PhpCsFixer\Config::create()
    ->setUsingCache(false)
    ->setRules(array(
            '@PSR2' => true,
            'array_syntax' => array('syntax' => 'short'),
            'full_opening_tag' => true,
        ))
    ->setFinder($finder)
;