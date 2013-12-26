<?php

namespace riabuilder\readers;

use riabuilder\components\FileLoader;

/**
 * Class BaseReader. Base class for reader. Reader loaded,
 * parse files and save it data to variable `result`.
 *
 * @author Vladimir Kozhin <affka@affka.ru>
 * @license MIT
 * @package riabuilder\readers
 */
abstract class BaseReader {

    /**
     * @type array
     */
    public $files = array();

    abstract public function getId();

    abstract public function load();

    /**
     * @type \riabuilder\PackageBuilder
     */
    protected $builder;

    /**
     * @type string
     */
    protected $result = '';

    /**
     * @type boolean
     */
    protected $isInitialized = false;

    /**
     * @type string
     */
    private $relativePath;

    public function __construct($builder, $relativePath = '') {
        $this->builder = $builder;
        $this->relativePath = $relativePath;

        $this->init();
    }

    public function getResult() {
        return $this->result;
    }

    /**
     * Set configure params, skip not find params
     * @param array $params Key-value list of params
     */
    public function configure($params) {
        $paramNames = $this->paramNames();
        foreach ($params as $key => $value) {
            if (in_array($key, $paramNames)) {
                $this->$key = $value;
            }
        }

    }

    /**
     * @param array
     * @return array
     */
    public function getParams($names = null) {
        $values = array();
        foreach ($this->paramNames() as $name) {
            $values[$name] = $this->$name;
        }

        if (is_array($names)) {
            $values2 = array();
            foreach ($names as $name) {
                $values2[$name] = isset($values[$name]) ? $values[$name] : null;
            }
            return $values2;
        }

        return $values;
    }

    protected function init() {
    }

    protected function getEndLineBreak() {
        return $this->builder->useCompress ? "" : "\n\n";
    }

    protected function getRelativePath() {
        return $this->relativePath;
    }

    protected function getAbsolutePath() {
        return $this->builder->rootPath . ($this->relativePath ? '/' . $this->relativePath : '');
    }

    /**
     * @return array
     */
    protected function paramNames() {
        $class = new \ReflectionClass(get_class($this));
        $names = array();
        foreach ($class->getProperties() as $property) {
            $name = $property->getName();
            if ($property->isPublic() && !$property->isStatic()) {
                $names[] = $name;
            }
        }

        return $names;
    }

    /**
     * @return array
     */
    protected function loadFilesData() {
	    $loader = new FileLoader();
	    $loader->root = $this->builder->rootPath;
	    $loader->availableExtensions = ReaderType::getExtensions($this->getId());

	    // Get module or root path
	    //$rootPath = $this->module ? $this->module->path : $this->builder->rootPath;
	    //$files = self::normalizeFilePath($this->files, $this->getAbsolutePath());

        $files = array();
        foreach ((array) $this->files as $file) {
            $files[] = static::normalizeFilePath($file, $this->getAbsolutePath());
        }

	    return $loader->load($files);
    }

    /**
     * Parse and normalize file path. Supported formats:
     *  - aa/bb.js Relative path
     *  - ./aa/bb.js The same of relative path
     *  - /aa/bb.js Root dir is base path (see module params)
     * @param array|string $file
     * @param string $moduleAbsolutePath
     * @return string
     */
    protected static function normalizeFilePath($file, $moduleAbsolutePath) {
        // Check absolute file path
        if (file_exists($file)) {
            return $file;
        }

        // Check file or dir mask from root path
        if (substr($file, 0, 1) === '/' || substr($file, 1, 2) === ':/') {
            return ltrim($file, '/');
        }

        // Remove `./` - other paths is relative of module
        if (substr($file, 0, 2) === './') {
            $file = substr($file, 2);
        }

        return $moduleAbsolutePath . '/' . $file;
    }

}