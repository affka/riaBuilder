<?php

namespace riabuilder\readers;

class JavaScriptReader extends BaseReader {

    /**
     * @type array
     */
    public $files = array();

    /**
     * @type string|null
     */
    public $browser;

    public function getId() {
        return ReaderType::JS;
    }

    public function load() {
        // Skip main file
        //FileLoader::$skipFiles = $this->package['main'] ? array($this->package['main']) : array();

        // Load js files
        $filesData = $this->loadFilesData();

        // Append to result
        $script = implode("\n\n", array_values($filesData));
        $this->append($script);
    }

    public function append($script) {
        // Wrap in browser condition, if set browser
        $script = $this->browser !== null ?
            "\nif (RIABuilder.matchBrowser(" . \CJSON::encode($this->browser) . ")) {\n" . $script . "\n}\n" :
            $script;

        // Wrap scripts block, if need
        if (!empty($params['wrap'])) {
            $script = $this->wrapScript($script);
        }

        if ($this->builder->useCompress) {
            require_once __DIR__ . '/../vendors/minify/min/lib/JSMin.php';
            $script = \JSMin::minify($script);
        }

        // Append scripts
        $this->result .= rtrim(trim($script), ';') . ';' . $this->getEndLineBreak();
    }

}