<?php

namespace riabuilder;

use riabuilder\readers\JavaScriptReader;
use riabuilder\readers\ModuleReader;

/**
 * Class PackageBuilder
 * Build js, css, less, html and other files in one package.
 * Package is JavaScript file, including all scripts and also css/html as text.
 * Css will be appender to document after load package.
 * Templates (htm/html files) will be available via javascript
 * method RIABuilder.getTemplate('relative/path/to/template.html');
 *
 * Module is a directory with file `package.json`.
 *
 * @author Vladimir Kozhin <affka@affka.ru>
 * @package riabuilder
 */
class PackageBuilder {

    public $rootPath;
    public $useCompress = false;

    public static function registerAutoloader() {
        spl_autoload_register(__CLASS__ . '::autoloader');
    }

    public static function autoloader($class) {
        if (strpos($class, 'riabuilder') === 0) {
            $class = str_replace('riabuilder\\', '', $class);
            $class = str_replace('\\', '/', $class);
            require_once __DIR__ . '/' . $class . '.php';
        }
    }

    /**
     * Main method for build module
     * @param string $path Relative path to module (see also `rootPath` param)
     * @return string JavaScript code
     */
    public function readModule($path) {
        // Load root RIABuilder scripts and append to package at first
        $jsReader = new JavaScriptReader($this, null);
        $jsReader->files = array(
            __DIR__ . '/assets/riabuilder.js',
        );
        $jsReader->load();

        // Load module files
        $reader = new ModuleReader($this, null);
        $reader->path = $path;
        $reader->load();

        return $jsReader->getResult() . $reader->getResult();
    }

}
