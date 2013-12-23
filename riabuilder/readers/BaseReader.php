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

    abstract public function getId();

    abstract public function load();

    /**
     * @type \riabuilder\PackageBuilder
     */
    protected $builder;

    /**
     * @type \riabuilder\readers\ModuleReader
     */
    protected $module;

    protected $result = '';

    public function __construct($builder, $module) {
        $this->builder = $builder;
        $this->module = $module;

        $this->init();
    }

    public function init() {
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

    protected function getEndLineBreak() {
        return $this->builder->useCompress ? "" : "\n\n";
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
	    // Get module or root path
	    $rootPath = $this->module ? $this->module->path : $this->builder->rootPath;

	    $loader = new FileLoader();
	    $loader->root = $rootPath;
	    $loader->availableExtensions = ReaderType::getExtensions($this->getId());

	    $files = self::normalizeFilePath($this->files, $rootPath);
	    return $loader->load($files);
    }

    /**
     * Parse and normalize file path. Supported formats:
     *  - aa/bb.js Relative path
     *  - ./aa/bb.js The same of relative path
     *  - /aa/bb.js Root dir is base path (see module params)
     * @param array|string $file
     * @param string $modulePath
     * @return string
     */
    protected static function normalizeFilePath($file, $modulePath) {
        if (is_array($file)) {
            foreach ($file as &$fileItem) {
                $fileItem = self::normalizeFilePath($fileItem, $modulePath);
            }
            return $file;
        }

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

        return $modulePath . '/' . $file;
    }

}