<?php

namespace riabuilder\readers;

class TemplateReader extends BaseReader {

    /**
     * @type array
     */
    public $files = array();

    public function getId() {
        return ReaderType::TEMPLATE;
    }

    public function load() {
        // Load css files
        $filesData = $this->loadFilesData();

        // Append templates
        if (count($filesData) > 0) {
            $this->append($filesData);
        }
    }

    public function append($templates) {
        if ($this->builder->useCompress) {
            require_once __DIR__ . '/../vendors/minify/min/lib/Minify/HTML.php';
            foreach ($templates as &$html) {
                $html = \Minify_HTML::minify($html);
            }
        }

        $this->result .= "RIABuilder.appendTemplates(" . \json_encode($templates) . ");" . $this->getEndLineBreak();
    }

}