<?php

namespace riabuilder\readers;

/**
 * Class LessReader
 *
 * @author Vladimir Kozhin <affka@affka.ru>
 * @package riabuilder\readers
 */
class LessReader extends BaseReader {

    /**
     * @type array
     */
    public $files = array();

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

        $cssReader = new CssReader($this->builder, $this->module);
        foreach ($filesData as $relativePath => $lessData) {
            $this->lessCompiler->setImportDir(array(
                $this->module->getAbsolutePath(),
                dirname($relativePath),
            ));
            $cssReader->append($this->lessCompiler->compile($lessData));
        }

        $this->result .= $cssReader->getResult();
    }

}