<?php

# launch with php  -d zend_extension=xdebug.so merge.php <file1> <file2> <output.xml>

require '../../vendor/autoload.php';

$api = require $argv[1]; /* @var $api PHP_CodeCoverage */
$client = require $argv[2];/* @var $api PHP_CodeCoverage */

$api->merge($client);

$writer = new PHP_CodeCoverage_Report_Clover();
$writer->process($coverage, $argv[3]);
