PHP RIA Builder
==========

PHP Application for build html, css, less, js files to one file.
Is very simplify method for compiling JavaScript application for production.

What is it, example
------------

Suppose we have a simple application

    #testapp/style_ie9.css
    body {
        color: red;
    }

    #testapp/main.js
    function a(message) {
        alert(message);
    }

Create package.json for build

    {
        "include": [
            {
                "type": "css",
                "files": [
                    "styles_ie9.css"
                ],
                "browser": "ie <9"
            },
            "*.js"
        ]
    }

Build package, for example through the console

    /var/www/riabuilder/run -c testapp

Compile result

    #testapp.js
    ...
    if (RIABuilder.matchBrowser("ie <9")){RIABuilder.appendStyle("body {color: red;}");} function a(m) {alert(m);}


Features
------------

- Supported formats: `js`, `css`, `less`, `html`
- Server-side compile less to css
- Compress js, css and html
- Include js/css/template by browser condition
- Dynamic build and return package
- Build package from command line
- Manually load packages from frontend

How to use
------------

### Create package.json file

Package (or module) - is a directory with package.json file, which has configuration for building application.
See package.json format example:

    {
        // Main section - include
        "include": [
            // Full format (for types: js, css, less, template)
            {
                // One of file type: js, css, less, template, module
                "type": "js",

                // List of included files
                "files": [
                    "components/*.js"
                ],

                // Wrap javascript included files in anonymous function? Default: false
                "wrap": false,

                // Wrap each javascript file in anonymous function? Default: false
                "wrapEach": true,

                // Browser condition, format:
                //   "browser [comparison][version]"
                //   - browser One of values: ie, chrome, safari, opera, firefox, ...
                //   - comparison Sing comparison: >=, <=, >, <, =
                //   - version Number of version
                // Examples:
                //   - ie 8
                //   - firefox >10.2
                //   - opera
                "browser": "ie <9"
            },

            // Full format (for type: module)
            {
                "type": "module",

                // Relative path to module dir or package.json file
                "files": "module/customapp",

                // Set this params as default for module
                "wrap": false,
                "wrapEach": true
            },

            // Load css only for ie < 9
            {
                "type": "css",
                "files": [
                    "styles_ie.css"
                ],
                "browser": "ie <9"
            },

            // Short format for load one file. Type autodetect by extension `js`.
            "main.js",

            // Short format for load templates. This item will be loaded all templates
            // in module dir and it sub dirs. Type auto detected by extension `html`.
            "*.html",

            // Will be loaded and convert to css all less files from dir less.
            "less/*.less"
        ]
    }

### Build from console

Format:

    run.bat [-c][-p] modulePath [savePath]

Flags:
- `-c` or `--useCompress` Enabled compress js, css and html for production mode
- `-p` or `--rootPath` Absolute path to root of javascript applications

Attributes:
- `modulePath` Relative path to module dir or package.json file. Examples: dir, dir/subdir, dir/mypackage.json. If set dir, then by default will be fined file `package.json`.
- `savePath` (optional) Relative path to save file. By default will be used modulePath without special chars. Example: dir/testapp -> dir_testapp.js.

Run commands:

    win:
    C:\www\riabuilder\run.bat -c testapp test_min.js
    
    linux:
    /var/www/riabuilder/run -c testapp test_min.js

### Dynamic build and echo to browser

You can create `PackageBuilder` instance and manually build package.
`PackageBuilder` - is a main class with global configurations:
- `rootPath` Absolute path to root of javascript applications. Relative to that directory will be searched you modules.
- `useCompress` Enable compressing js, css and html.
- method `readModule` Scan module package.json, require all resources and returned JavaScript code.

Example:

    <?php

    require_once __DIR__ . '/../riabuilder/PackageBuilder.php';
	
    \riabuilder\PackageBuilder::registerAutoloader();

    $packageBuilder = new \riabuilder\PackageBuilder();
    $packageBuilder->rootPath = __DIR__;
    //$packageBuilder->useCompress = true;

    header('Content-Type: text/javascript; charset=UTF-8');
    echo $packageBuilder->readModule('testapp');


Requirements
------------

- Server: PHP 5.3 or latest
- Client: Tested on browsers ie >8 and last versions of opera, chrome, safari, firefox