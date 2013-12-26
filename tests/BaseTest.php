<?php

require_once __DIR__ . '/../riabuilder/PackageBuilder.php';
\riabuilder\PackageBuilder::registerAutoloader();

abstract class BaseTest extends PHPUnit_Framework_TestCase {

    protected $packageBuilder;

    public function setUp() {
        $this->packageBuilder = new \riabuilder\PackageBuilder();
        $this->packageBuilder->rootPath = __DIR__ . '/tmp';
        $this->packageBuilder->excludeRiaBuilderJs = true;
        $this->packageBuilder->useCompress = true;
    }

    public function tearDown() {
        $dir = $this->packageBuilder->rootPath;
        foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST) as $path) {
            $path->isFile() ? unlink($path->getPathname()) : rmdir($path->getPathname());
        }
        rmdir($dir);
    }

    protected function createFile($path, $data) {
        $path = ltrim($path, '/');
        $path = $this->packageBuilder->rootPath . '/' . $path;

        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 777, true);
        }

        file_put_contents($path, $data);
    }

}
