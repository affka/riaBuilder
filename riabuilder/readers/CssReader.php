<?php

namespace riabuilder\readers;

use riabuilder\components\FileLoader;

class CssReader extends BaseReader {

    /**
     * @type array
     */
    public $files = array();

    public function getId() {
        return ReaderType::CSS;
    }

    public function load() {
        // Load css files
        $filesData = $this->loadFilesData();

        // Append to package css
        if (count($filesData) > 0) {
            $css = implode("\n\n", array_values($filesData));
            $this->append($css);
        }
    }

    public function append($cssRules) {
        $cssRules = trim($cssRules);

        if ($cssRules) {
            if ($this->builder->useCompress) {
                require_once __DIR__ . '/../vendors/minify/min/lib/CSSmin.php';
                $CSSmin = new \CSSmin();
                $cssRules = $CSSmin->run($cssRules);
            }

            $this->result .= "RIABuilder.appendStyle(" . \json_encode($cssRules) . ");" . $this->getEndLineBreak();
        }
    }

}