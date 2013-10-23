<?php

require_once __DIR__ . '/../riabuilder/PackageBuilder.php';

\riabuilder\PackageBuilder::registerAutoloader();

$packageBuilder = new \riabuilder\PackageBuilder();
$packageBuilder->rootPath = __DIR__;
//$packageBuilder->useCompress = true;

header('Content-Type: text/javascript; charset=UTF-8');
echo $packageBuilder->readModule('testapp');
