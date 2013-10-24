<?php

namespace riabuilder\readers;

/**
 * Class ModuleReader
 *
 * @author Vladimir Kozhin <affka@affka.ru>
 * @license MIT
 * @package riabuilder\readers
 */
class ModuleReader extends BaseReader {

    public $path;
    public $include = array();

    public $wrap = false;
    public $wrapEach = false;

    public function getId() {
        return ReaderType::MODULE;
    }

    public function getPath() {
        return $this->path;
    }

    public function getAbsolutePath() {
        return $this->builder->rootPath . '/' . $this->path;
    }

    public function load() {
        $packageFilePath = self::normalizePackageFilePath($this->path);
        $this->path = dirname($packageFilePath);

        // Load
        $data = self::loadJson($packageFilePath);
        $this->include = isset($data['include']) ? (array) $data['include'] : array();
        $this->include = self::normalizeIncludeFormat($this->include);

        $this->processInclude();
    }

    public function processInclude() {
        foreach ($this->include as $params) {
            if (empty($params['type'])) {
                throw new \Exception('Not find type for module `' . $this->path . '`, section `' . json_encode($params) . '`.');
            }

            // Create reader instance
            $className = ReaderType::getClassName($params['type']);
            $reader = new $className($this->builder, $this);

            // Extend global params
            $reader->configure($this->getParams(array(
                'wrap',
                'wrapEach',
            )));

            // Set custom params
            $reader->configure($params);

            // Run reader logic
            $reader->load();
            $this->result .=  $reader->getResult();
        }
    }

    public static function normalizeIncludeFormat(array $include) {
        $formattedInclude = array();
        foreach ($include as $params) {
            if (is_array($params)) {
                $formattedInclude[] = $params;
            } elseif (is_string($params)) {
                if (!preg_match('/([a-z]+)$/i', $params, $match)) {
                    throw new \Exception('Not find extension for path `' . $params . '`');
                }

                $formattedInclude[] = array(
                    'type' => ReaderType::findByExtension($match[1]),
                    'files' => array($params),
                );
            }
        }
        return $formattedInclude;
    }

    /**
     * Load and parse json file. Check exists and validate parsing
     * @param string $file
     * @return string|array
     * @throws \Exception
     */
    public static function loadJson($file) {
        // Read
        $value = file_get_contents($file);
        if ($value === false) {
            throw new \Exception('Cannot read file ' . $file);
        }

        // Decode
        $result = json_decode($value, true);
        if ($result === null) {
            throw new \Exception('Corrupted JSON in ' . $file);
        }

        return $result;
    }

    protected static function normalizePackageFilePath($path) {
        $path = trim($path, '/');

        // Append /package.json, if no set custom
        if (substr($path, -5) !== '.json') {
            $path .= '/package.json';
        }

        return $path;
    }

}