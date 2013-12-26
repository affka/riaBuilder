<?php

namespace riabuilder\readers;

/**
 * Class LessReader
 *
 * @author Vladimir Kozhin <affka@affka.ru>
 * @license MIT
 * @package riabuilder\readers
 */
class LessReader extends BaseReader {

    private $lessCompiler;

    public function init() {
        // Require less compiler and create instance
        require_once __DIR__ . "/../vendors/lessphp/lessc.inc.php";
        $this->lessCompiler = new \lessc();
    }

    public function getId() {
        return ReaderType::LESS;
    }

    public function load() {
	    // Load less files
	    $filesData = $this->loadFilesData();
        foreach (array_keys($filesData) as $relativePath) {
            $this->setImportDir($relativePath);
        }

        $lessData = implode("\n", array_values($filesData));
        $this->loadData($lessData);
    }

    public function loadData($lessData) {
        $cssReader = new CssReader($this->builder, $this->getRelativePath());
        $cssReader->loadData($this->lessCompiler->compile($lessData));

        $this->result .= $cssReader->getResult();
    }

    public function setImportDir($dir) {
        $this->lessCompiler->setImportDir(array(
            $this->getAbsolutePath(),
            dirname($dir),
        ));
    }

}