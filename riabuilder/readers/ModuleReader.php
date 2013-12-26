<?php

namespace riabuilder\readers;

/**
 * Class ModuleReader
 *
 * @author Vladimir Kozhin <affka@affka.ru>
 * @license MIT
 * @package riabuilder\readers
 */
class ModuleReader extends JavaScriptReader {

    public function getId() {
        return ReaderType::MODULE;
    }

    public function load() {
        // Load js files
        $filesData = $this->loadFilesData();

        // Load
        $jsonData = array();
        foreach ($filesData as $file => $data) {
            $relativePath = dirname($file);
            if ($relativePath === '.') {
                $relativePath = '';
            }

            $jsonData[$relativePath] = self::parseJson($data);
        }

        $this->loadData($jsonData);
    }

    public function loadData($filesData) {
        $allJsCode = '';

        foreach ($filesData as $relativePath => $jsonData) {
            $include = isset($jsonData['include']) ? (array) $jsonData['include'] : array();
            $jsCode = $this->processInclude($relativePath, $include, !empty($jsonData['wrapEach']));

            if ($this->wrapEach || !empty($jsonData['wrap'])) {
                $jsCode = $this->wrapScript($jsCode);
            }
            $allJsCode .= $jsCode;
        }

        if ($this->wrap) {
            $allJsCode = $this->wrapScript($allJsCode);
        }
        $this->result .= $allJsCode;
    }

    public function processInclude($relativePath, $include, $wrapEach = false) {
        $include = self::normalizeIncludeFormat($include);
        $jsCode = '';

        foreach ($include as $params) {
            /** @type riabuilder/readers/BaseReader */
            $className = ReaderType::getClassName($params['type']);

            if ($className === null) {
                throw new \Exception('Not find type for module `' . $this->getAbsolutePath() . '`, section `' . json_encode($params) . '`.');
            }

            // Create reader instance
            $reader = new $className($this->builder, $relativePath);

            // Set custom params
            $reader->configure($params);

            // Run reader logic
            $reader->load();

            $script = $reader->getResult();
            if ($wrapEach) {
                $script = $this->wrapScript($script);
            }
            $jsCode .= $script;
        }

        return $jsCode;
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
     * @param string $stringData
     * @return string|array
     * @throws \Exception
     */
    public static function parseJson($stringData) {
        // Decode
        $result = json_decode($stringData, true);
        if ($result === null) {
            throw new \Exception('Corrupted JSON in ' . $stringData);
        }

        return $result;
    }

    protected static function normalizeFilePath($file, $moduleAbsolutePath) {
        $file = parent::normalizeFilePath($file, $moduleAbsolutePath);
        $file = rtrim($file, '/');

        // Append /package.json, if no set custom
        if (substr($file, -5) !== '.json') {
            $file .= '/package.json';
        }

        return $file;
    }

}