<?php

global $testCase;
$testCase = 'table_for_test';

$pathToDocumentRoot = realpath(__DIR__ . '/../../../../../');
var_dump($pathToDocumentRoot);
chdir($pathToDocumentRoot);

include $pathToDocumentRoot . '/scripts/scriptProxy.php';