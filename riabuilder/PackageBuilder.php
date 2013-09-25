<?php

namespace riabuilder;

use riabuilder\readers\ModuleReader;

/**
 * Class Builder
 * Build js, css, less, html and other files in one package.
 * Package is JavaScript file, including all scripts and also css/html as text.
 * Css will be appender to document after load package.
 * Templates (htm/html files) will be available via javascript method RIABuilder.getTemplate('relative/path/to/template.html');
 *
 * Module is a directory with file `package.json`.
 *
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

    public function readModule($path) {
        $reader = new ModuleReader($this, null);
        $reader->path = $path;
        $reader->load();

        return $reader->getResult();
    }






	private static $isJavaScriptRIABuilderInit = false;


	/**
	 * Return package as text
	 * @return string
	 */
	public function getText() {
		return $this->result;
	}


	/**
	 * Method include files and save in `result` param
	 */
	protected function initInclude() {
		// Parse and normalize files main file path
		if ($this->package['main'] !== false) {
			$this->package['main'] = $this->normalizeFilePath($this->package['main']);
		}

		// Include simple types
		foreach ($this->package['include'] as $params) {

			// Load root RIABuilder scripts and append to package at first
			if (self::$isJavaScriptRIABuilderInit === false) {
				$this->result .= file_get_contents(__DIR__ . '/../assets/riabuilder.js') . "\n\n";
				self::$isJavaScriptRIABuilderInit = true;
			}

		}

		// At the end append main script, if it exists
		if ($this->package['main'] !== false) {
			$this->result .= file_get_contents(\Yii::app()->getModule('riabuilder')->riaBasePath . '/' . $this->package['main']) . "\n\n";
		}

		// Wrap all package, if needs
		if ($this->package['wrap'] === true) {
			$this->result = $this->wrapScript($this->result);
		}
	}

	/**
	 * Wrap script to function
	 * @param string $script
	 * @return string
	 */
	protected function wrapScript($script) {
		return "(function() {\n\t" . str_replace("\n", "\n\t", $script) . "\n})();";
	}
}
