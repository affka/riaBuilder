<?php

namespace riabuilder\readers;

/**
 * Class TemplateReader
 *
 * @author Vladimir Kozhin <affka@affka.ru>
 * @license MIT
 * @package riabuilder\readers
 */
class TemplateReader extends BaseReader {

	/**
	 * @type array
	 */
	public $files = array();

	/**
	 * Browser condition
	 * @type string
	 */
	public $browser;

	/**
	 * Root path templates for searching in javascript RIABuilder.getTemplate(..)
	 * @type string
	 */
	public $searchPath = false;

	public function getId() {
		return ReaderType::TEMPLATE;
	}

	public function load() {
		// Load css files
		$filesData = $this->loadFilesData();

		// Append templates
		if (count($filesData) > 0) {
			$filesData = $this->normalizeTemplatesPath($filesData);
			$this->append($filesData);
		}
	}

	public function append($templates) {
		// Compress html code, if need
		if ($this->builder->useCompress) {
			require_once __DIR__ . '/../vendors/minify/min/lib/Minify/HTML.php';
			foreach ($templates as &$html) {
				$html = \Minify_HTML::minify($html);
			}
		}

		$script = "RIABuilder.appendTemplates(" . \json_encode($templates) . ");" . $this->getEndLineBreak();

		// Add html as script via JavaScriptReader
		$javaScriptReader = new JavaScriptReader($this->builder, $this->module);
		$javaScriptReader->configure($this->getParams(array(
			'browser',
		)));
		$javaScriptReader->append($script);

		$this->result .= $javaScriptReader->getResult();
	}

	protected function normalizeTemplatesPath($filesData) {
		if ($this->searchPath === false) {
			return $filesData;
		}

		$rootPath = $this->module ? $this->module->path : $this->builder->rootPath;
		$nameRootPath = self::normalizeFilePath($this->searchPath, $rootPath);

		$data = array();
		foreach ($filesData as $path => $html) {
			$path = str_replace($nameRootPath . '/', '', $rootPath . '/' . $path);

			$data[$path] = $html;
		}
		return $data;
	}

}