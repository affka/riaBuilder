<?php

namespace riabuilder\readers;

/**
 * Class JavaScriptReader
 *
 * @author Vladimir Kozhin <affka@affka.ru>
 * @license MIT
 * @package riabuilder\readers
 */
class JavaScriptReader extends BaseReader {

    /**
     * @type string|null
     */
    public $browser;

    public $wrap = false;
    public $wrapEach = false;

    public function getId() {
        return ReaderType::JS;
    }

    public function load() {
        // Load js files
        $filesData = $this->loadFilesData();

        // Wrap each scripts, if need
        if ($this->wrapEach) {
            foreach ($filesData as &$scriptItem) {
                $scriptItem = $this->wrapScript($scriptItem);
            }
        }

        // End script symbol - always `;`
        $scripts = array();
        foreach (array_values($filesData) as $script) {
            $script = rtrim(trim($script), ';') . ';';
            $scripts[] = $script;
        }

        // Append to result
        $this->loadData(implode('', $scripts));
    }

    public function loadData($script) {
        $this->append($script);
    }

    public function append($script) {
        // Wrap in browser condition, if set browser
        if ($this->browser !== null) {
            // @todo Match browser syntax
            $script = self::wrapBrowserMatch($script, $this->browser);
        }

        // Wrap scripts block, if need
        if ($this->wrap) {
            $script = $this->wrapScript($script);
        }

        if ($this->builder->useCompress) {
            require_once __DIR__ . '/../vendors/minify/min/lib/JSMin.php';
            $script = \JSMin::minify($script);
        }

        // Append scripts
        $this->result .= $script . $this->getEndLineBreak();
    }

    /**
     * @param string $script
     * @param string $browser
     * @return string
     */
    protected static function wrapBrowserMatch($script, $browser) {
        return "\nif (RIABuilder.matchBrowser(" . \json_encode($browser) . ")) {\n\t" . str_replace("\n", "\n\t", $script) . "\n}\n";
    }

    /**
     * Wrap script to function
     * @param string $script
     * @return string
     */
    protected function wrapScript($script) {
        return $this->builder->useCompress ?
            "(function(){" . $script . "})();" :
            "(function() {\n\t" . str_replace("\n", "\n\t", $script) . "\n})();";
    }

}