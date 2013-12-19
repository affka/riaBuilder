<?php

/**
 * Builder console bootstrap file
 * Available options:
 *  - useCompress
 *  - rootPath
 *
 * Available flags:
 *  - c useCompress
 *
 * Last arguments:
 *  1. Relative path to module
 *  2. Save file path (optional)
 * @author Vladimir Kozhin <affka@affka.ru>
 */

// Load library
require_once __DIR__ . '/PackageBuilder.php';
\riabuilder\PackageBuilder::registerAutoloader();

// Parse arguments
$params = \riabuilder\components\ConsoleArguments::parse($argv);
$files = array_merge($params['arguments'], $params['commands']);

// Create package builder instance
$packageBuilder = new \riabuilder\PackageBuilder();
$packageBuilder->rootPath = !empty($params['options']['rootPath']) ? $params['options']['rootPath'] : getcwd();
$packageBuilder->useCompress = !empty($params['options']['useCompress']) || in_array('c', $params['flags']);

// Check required argument - module relative path
if (count($files) === 0) {
    echo "Wrong format. Please write relative module path at first argument.";
    exit(1);
}

// Run builder
$jsCode = $packageBuilder->readModule($files[0]);

// Save result in file
$savePath = isset($files[1]) ? $files[1] : preg_replace('/[^a-z0-9-_]i/', '', $files[0]) . ($packageBuilder->useCompress ? '_min' : '') . '.js';
file_put_contents($packageBuilder->rootPath . '/' . $savePath, $jsCode);

exit(0);