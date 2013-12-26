<?php

require_once __DIR__ . '/BaseTest.php';

class PackageTest extends BaseTest {

    public function testReaders() {
        $this->createFile('a.js', 'js');
        $this->createFile('a.css', 'css');
        $this->createFile('a.less', 'less');
        $this->createFile('a.html', 'template');

        $this->createFile('packageFullSyntax.json', json_encode(array(
            'include' => array(
                array(
                    'type' => 'js',
                    'files' => array(
                        'a.js',
                    ),
                ),
                array(
                    'type' => 'css',
                    'files' => array(
                        'a.css',
                    ),
                ),
                array(
                    'type' => 'less',
                    'files' => array(
                        'a.less',
                    ),
                ),
                array(
                    'type' => 'template',
                    'files' => array(
                        'a.html',
                    ),
                ),
            ),
        )));
        $this->createFile('packageShortSyntax.json', json_encode(array(
            'include' => array(
                'a.js',
                'a.css',
                'a.less',
                'a.html',
            ),
        )));

        $expected = 'js;RIABuilder.appendStyle("css");RIABuilder.appendTemplates({"a.html":"template"});';

        $js = $this->packageBuilder->readModule('packageFullSyntax.json');
        $this->assertEquals($expected, $js);

        $js = $this->packageBuilder->readModule('packageShortSyntax.json');
        $this->assertEquals($expected, $js);
    }

    public function testModuleReader() {
        $this->createFile('m/a.js', 'ajs');
        $this->createFile('m/package.json', json_encode(array(
            'include' => array(
                'a.js',
            ),
        )));

        $this->createFile('b.js', 'bjs');
        $this->createFile('fullPackage.json', json_encode(array(
            'include' => array(
                array(
                    'type' => 'module',
                    'files' => array('m'),
                ),
                array(
                    'type' => 'js',
                    'files' => array('b.js'),
                ),
            ),
        )));
        $this->createFile('shortPackage.json', json_encode(array(
            'include' => array(
                'm/*.json',
                'b.js',
            ),
        )));

        $expected = 'ajs;bjs;';

        $js = $this->packageBuilder->readModule('fullPackage.json');
        $this->assertEquals($expected, $js);

        $js = $this->packageBuilder->readModule('shortPackage.json');
        $this->assertEquals($expected, $js);
    }

    public function testSubMobules() {
        $this->createFile('m/sm/sma.js', 'smjs');
        $this->createFile('m/sm/sma.css', 'smcss');
        $this->createFile('m/sm/sma.less', 'smless');
        $this->createFile('m/sm/sma.html', 'smtemplate');
        $this->createFile('m/sm/package.json', json_encode(array(
            'include' => array(
                'sma.js',
                'sma.css',
                'sma.less',
                'sma.html',
            ),
        )));

        $this->createFile('m/ma.js', 'mjs');
        $this->createFile('m/ma.css', 'mcss');
        $this->createFile('m/ma.less', 'mless');
        $this->createFile('m/ma.html', 'mtemplate');
        $this->createFile('m/package.json', json_encode(array(
            'include' => array(
                'ma.js',
                'ma.css',
                'ma.less',
                'ma.html',
                'sm/package.json',
            ),
        )));


        $this->createFile('a.js', 'js');
        $this->createFile('a.css', 'css');
        $this->createFile('a.less', 'less');
        $this->createFile('a.html', 'template');
        $this->createFile('package.json', json_encode(array(
            'include' => array(
                'a.js',
                'a.css',
                'a.less',
                'a.html',
                'm/package.json',
            ),
        )));

        $expected = 'js;RIABuilder.appendStyle("css");RIABuilder.appendTemplates({"a.html":"template"});mjs;RIABuilder.appendStyle("mcss");RIABuilder.appendTemplates({"m\/ma.html":"mtemplate"});smjs;RIABuilder.appendStyle("smcss");RIABuilder.appendTemplates({"m\/sm\/sma.html":"smtemplate"});';

        $js = $this->packageBuilder->readModule('');
        $this->assertEquals($expected, $js);
    }

    public function testLessVariables() {
        $this->createFile('params.less', '@color: red;');
        $this->createFile('style.less', 'body { background: @color; }');
        $this->createFile('package.json', json_encode(array(
            'include' => array(
                '*.less',
            ),
        )));

        $expected = 'RIABuilder.appendStyle("body{background:red}");';

        $js = $this->packageBuilder->readModule('');
        $this->assertEquals($expected, $js);
    }

    public function testTemplateSearchPath() {
        $this->createFile('themes/dark/index.html', 'index');
        $this->createFile('themes/dark/pages/about.html', 'about');
        $this->createFile('package.json', json_encode(array(
            'include' => array(
                array(
                    'type' => 'template',
                    'searchPath' => 'themes/dark',
                    'files' => '*',
                ),
            ),
        )));

        $expected = 'RIABuilder.appendTemplates({"index.html":"index","pages\/about.html":"about"});';

        $js = $this->packageBuilder->readModule('');
        $this->assertEquals($expected, $js);
    }

    public function testJavascriptWrap() {
        $this->createFile('wrap/a.js', 'a()');
        $this->createFile('wrap/b.js', 'b()');
        $this->createFile('wrap/package.json', json_encode(array(
            'include' => array(
                array(
                    'type' => 'js',
                    'files' => '*.js',
                    'wrap' => true,
                    'wrapEach' => true,
                ),
            ),
        )));

        $expected = '(function(){(function(){a()})();(function(){b()})();})();';

        $js = $this->packageBuilder->readModule('wrap');
        $this->assertEquals($expected, $js);
    }

    public function testModuleWrap() {
        $this->createFile('wrap/a.js', 'a1()');
        $this->createFile('wrap/b.js', 'b1()');
        $this->createFile('wrap/c.css', 'body {color:red}');
        $this->createFile('wrap/package.json', json_encode(array(
            'include' => array(
                '*.js',
                '*.css',
            ),
        )));

        $this->createFile('wrap2/a.js', 'a2()');
        $this->createFile('wrap2/b.js', 'b2()');
        $this->createFile('wrap2/c.css', 'body {color:green}');
        $this->createFile('wrap2/package.json', json_encode(array(
            'include' => array(
                '*.js',
                '*.css',
            ),
        )));

        $this->createFile('packageAsType.json', json_encode(array(
            'include' => array(
                array(
                    'type' => 'module',
                    'files' => array(
                        'wrap',
                        'wrap2',
                    ),
                    'wrapEach' => true,
                    'wrap' => true,
                ),
            ),
        )));
        $this->createFile('packageAsInclude.json', json_encode(array(
            'include' => array(
                'wrap/package.json',
                'wrap2/package.json',
            ),
            'wrapEach' => true,
            'wrap' => true,
        )));

        $expected = '(function(){(function(){a1();b1();RIABuilder.appendStyle("body{color:red}");})();(function(){a2();b2();RIABuilder.appendStyle("body{color:green}");})();})();';

        $js = $this->packageBuilder->readModule('packageAsType.json');
        $this->assertEquals($expected, $js);

        $js = $this->packageBuilder->readModule('packageAsInclude.json');
        $this->assertEquals($expected, $js);
    }

    public function testBrowserMatch() {
        $this->createFile('style.css', 'css');
        $this->createFile('ie6.css', 'ie');
        $this->createFile('package.json', json_encode(array(
            'include' => array(
                'style.css',
                array(
                    'type' => 'css',
                    'files' => array('ie6.css'),
                    'browser' => 'ie <=6',
                ),
            ),
        )));

        $expected = 'RIABuilder.appendStyle("css");if(RIABuilder.matchBrowser("ie <=6")){RIABuilder.appendStyle("ie");}';

        $js = $this->packageBuilder->readModule('');
        $this->assertEquals($expected, $js);
    }

}
