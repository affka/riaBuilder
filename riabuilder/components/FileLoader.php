<?php

namespace riabuilder\components;

/**
 * Class FileLoader
 * Load file or files list by mask
 *
 * @package riabuilder\components
 */
class FileLoader {

	/**
	 * Root directory for search dir and files
	 * @var string
	 */
	public $root;

	/**
	 * Skip file by it path
	 * @var array
	 */
	public $skipFiles = array();

	/**
	 * Filtering file by extension
	 * Supported formats:
	 *  - false Disabled filter
	 *  - string One extension or list, separated by comma. Example: 'js' or 'js,html,css'
	 *  - array Array of extensions
	 * @var bool|string|array
	 */
	public $availableExtensions = false;

	/**
	 * Load files and dirs
	 * Format examples
	 *  - lib/jquery/jquery.js
	 *  - lib/components/
	 *  - lib/*
	 * @param array $paths
	 * @return array Key-value list, where key is relative file path, and value is file data
	 */
	public function load($paths) {
		$files = array();

		foreach ($paths as $path) {
			$recursive = false;

			if (preg_match('/\*[^\/]*$/i', $path)) {
				$path = dirname($path);
				$recursive = true;
			}

			$files = array_merge($files, (array) $this->loadResource($this->getRootPath() . '/' . $path, $recursive));
		}
		return $files;
	}

    /**
     * @return string
     * @throws \Exception
     */
    private function getRootPath() {
		if ($this->root === null) {
			throw new \Exception('Not find root path, please set it.');
		}

		return $this->root;
	}

	/**
	 * Load dir (and sub dirs, if need) files
	 * @param string $dir File or directory for search
	 * @param boolean $recursive Search files in sub dirs
	 * @return array|null
	 */
	private function loadResource($dir, $recursive) {
		// If is file, load and return it
		if (!is_dir($dir)) {
			// Skip already loaded
			if (in_array($dir, $this->skipFiles)) {
				return null;
			}
			$this->skipFiles[] = $dir;

			// Check extension
			$extension = preg_replace('/.+\.([a-z]+)$/i', '$1', $dir);
			if (is_string($this->availableExtensions)) {
				$this->availableExtensions = explode(',', $this->availableExtensions);
			}
			if (is_array($this->availableExtensions) && !in_array($extension, $this->availableExtensions)) {
				return null;
			}

			// Get related path
			$relatedPath = str_replace($this->root, '', $dir);
			$relatedPath = trim($relatedPath, '/');

			return array($relatedPath => file_get_contents($dir));
		}

		// Store result data here
		$files = array();

		// Normalize dir path
		$dir = rtrim($dir, '/');

		// Scan directory
		foreach (scandir($dir) as $resource) {
			// Skip trash
			if ($resource == '.' || $resource == '..') {
				continue;
			}

			// Skip directory, if recursive scan is disabled
			if ($recursive === false && is_dir($dir . '/' . $resource)) {
				continue;
			}

			// Load next and append to files
			$returnData = self::loadResource($dir . '/' . $resource, $recursive);
			if (is_array($returnData)) {
				$files = array_merge($files, $returnData);
			}
		}

		return $files;
	}

}
