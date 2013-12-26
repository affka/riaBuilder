<?php

namespace riabuilder\readers;

use riabuilder\components\FileLoader;

/**
 * Class CssReader
 *
 * @author Vladimir Kozhin <affka@affka.ru>
 * @license MIT
 * @package riabuilder\readers
 */
class CssReader extends BaseReader {

    public $browser;

    public function getId() {
        return ReaderType::CSS;
    }

    public function load() {
        // Load css files
        $filesData = $this->loadFilesData();

        // Append to package css
        if (count($filesData) > 0) {
            $css = implode("\n\n", array_values($filesData));
            $this->loadData($css);
        }
    }

    public function loadData($css) {
        $this->append($css);
    }

    public function append($cssRules) {
        $cssRules = trim($cssRules);

        if ($cssRules) {
            if ($this->builder->useCompress) {
                require_once __DIR__ . '/../vendors/minify/min/lib/CSSmin.php';
                $CSSmin = new \CSSmin();
                $cssRules = $CSSmin->run($cssRules);
            }

            $script = "RIABuilder.appendStyle(" . \json_encode($cssRules) . ");" . $this->getEndLineBreak();

            // Add styles as script via JavaScriptReader
            $javaScriptReader = new JavaScriptReader($this->builder, $this->getRelativePath());
            $javaScriptReader->configure($this->getParams(array(
                'browser',
            )));
            $javaScriptReader->append($script);

            $this->result .= $javaScriptReader->getResult();
        }
    }

}