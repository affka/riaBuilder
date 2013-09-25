<?php

require_once __DIR__ . '/riabuilder/PackageBuilder.php';

\riabuilder\PackageBuilder::registerAutoloader();

$packegeBuilder = new \riabuilder\PackageBuilder();
$packegeBuilder->rootPath = __DIR__;
$packegeBuilder->useCompress = true;

header('Content-Type: text/javascript; charset=UTF-8');
echo $packegeBuilder->readModule('testapp');