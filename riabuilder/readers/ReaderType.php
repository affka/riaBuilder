<?php

namespace riabuilder\readers;

/**
 * Class ReaderType. Enum with all reader types.
 *
 * @author Vladimir Kozhin <affka@affka.ru>
 * @license MIT
 * @package riabuilder\readers
 */
class ReaderType {

    const JS = 'js';
    const CSS = 'css';
    const LESS = 'less';
    const TEMPLATE = 'template';
    const MODULE = 'module';

    protected static function getData() {
        return array(
            self::JS => array(
                'className' => 'riabuilder\readers\JavaScriptReader',
                'extensions' => array('js'),
            ),
            self::CSS => array(
                'className' => 'riabuilder\readers\CssReader',
                'extensions' => array('css'),
            ),
            self::LESS => array(
                'className' => 'riabuilder\readers\LessReader',
                'extensions' => array('less'),
            ),
            self::TEMPLATE => array(
                'className' => 'riabuilder\readers\TemplateReader',
                'extensions' => array('htm', 'html'),
            ),
            self::MODULE => array(
                'className' => 'riabuilder\readers\ModuleReader',
                'extensions' => array('json'),
            ),
        );
    }

    /**
     * Get reader class name by id
     * @param string $id
     * @return \riabuilder\readers\BaseReader
     */
    public static function getClassName($id) {
        $data = self::getData();
        return isset($data[$id]) ? $data[$id]['className'] : null;
    }

    /**
     * Get reader available extensions by id
     * @param string $id
     * @return array
     */
    public static function getExtensions($id) {
        $data = self::getData();
        return isset($data[$id]) ? $data[$id]['extensions'] : null;
    }

    /**
     * Find reader id by file extension
     * @param string $extension
     * @return string
     * @throws \Exception
     */
    public static function findByExtension($extension) {
        foreach (self::getData() as $id => $params) {
            if (in_array($extension, $params['extensions'])) {
                return $id;
            }
        }

        throw new \Exception('Not find reader for extension `' . $extension . '`.');
    }

}