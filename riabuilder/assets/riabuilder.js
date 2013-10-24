/**
 * JavaScript global class for manage package
 * @author Vladimir Kozhin <affka@affka.ru>
 * @license MIT
 */
(function () {

    // Skip, if this script already included
    if (window.RIABuilder) {
        return;
    }

    // Set RIABuilder as global static class
    var RIABuilder = window.RIABuilder = {

        /**
         * Bootstrap data (from backend)
         * @type {object}
         */
        bootstrapData: {},

        _config: {},
        _templates: {},

        init: function (config) {
            this._config = config;
        },

        /**
         * Append css rules to document
         * @param {string} styles
         */
        appendStyle: function (styles) {
            var css = document.createElement('style');
            css.type = 'text/css';

            if (css.styleSheet) css.styleSheet.cssText = styles;
            else css.appendChild(document.createTextNode(styles));

            document.getElementsByTagName("head")[0].appendChild(css);
        },

        /**
         * Append templates for application. See RIABuilder.getTemplate()
         * for getting template html.
         * @param {object} templates
         */
        appendTemplates: function (templates) {
            for (var name in templates) {
                if (!templates.hasOwnProperty(name)) {
                    continue;
                }

                this._templates[name] = templates[name];
            }
        },

        /**
         * Append bootstrap data. Method merge (non recursive) data
         * with previous appended data.
         * @param {object} data
         */
        appendBootstrapData: function (data) {
            for (var name in data) {
                if (!data.hasOwnProperty(name)) {
                    continue;
                }

                this.bootstrapData[name] = data[name];
            }
        },

        /**
         * Get template html by relative path
         * @param {string} name
         * @returns {string|null}
         */
        getTemplate: function (name) {
            if (!this._templates[name]) {
                throw new Error('Not find template `' + name + '`');
            }

            return this._templates[name];
        },

        /**
         * Match browser and it version
         * @param {string} name
         * @returns {boolean}
         */
        matchBrowser: function (name) {
            var params = name.split(' ');
            var browser = params[0];
            var version = params[1] || null;

            // match browser
            if (this.BrowserDetect.browser.toLowerCase() !== browser.toLowerCase()) {
                return false;
            }

            // match version
            if (version !== null) {
                var conditions = {
                    '>=': function (a, b) {
                        return a >= b;
                    },
                    '<=': function (a, b) {
                        return a <= b;
                    },
                    '>': function (a, b) {
                        return a > b;
                    },
                    '<': function (a, b) {
                        return a < b;
                    },
                    '=': function (a, b) {
                        return a == b;
                    }
                };

                for (var i in conditions) {
                    if (!conditions.hasOwnProperty(i)) {
                        continue;
                    }

                    if (version.indexOf(i) !== -1) {
                        version = version.replace(i, '');
                        return conditions[i].call(this, this.BrowserDetect.version, version);
                    }
                }
                return version == this.BrowserDetect.version;
            }

            return true;
        },

        /**
         * Manually load module from JavaScript
         * @param {string} path Relative path to module dir or package.json file
         * @param {function} [callback]
         */
        loadModule: function (path, callback) {
            var script = document.createElement('script');
            var loaded = false;
            var src = this._config.buildLinkTemplate.replace('CUSTOM_PATH', path);

            script.setAttribute('src', src);
            if (callback) {
                script.onreadystatechange = script.onload = function () {
                    if (!loaded) {
                        callback();
                    }
                    loaded = true;
                };
            }
            document.getElementsByTagName('head')[0].appendChild(script);
        }
    };

    RIABuilder.BrowserDetect = {
        init: function () {
            this.browser = this.searchString(this.dataBrowser) || "An unknown browser";
            this.version = this.searchVersion(navigator.userAgent) || this.searchVersion(navigator.appVersion) || "an unknown version";
            this.OS = this.searchString(this.dataOS) || "an unknown OS";
        },
        searchString: function (data) {
            for (var i = 0; i < data.length; i++) {
                var dataString = data[i][0];
                var dataProp = data[i][5];
                this.versionSearchString = data[i][4] || data[i][3];
                if (dataString) {
                    if (dataString.indexOf(data[i][1]) != -1)
                        return data[i][3];
                }
                else if (dataProp)
                    return data[i][3];
            }
        },
        searchVersion: function (dataString) {
            var index = dataString.indexOf(this.versionSearchString);
            if (index == -1) return;
            return parseFloat(dataString.substring(index + this.versionSearchString.length + 1));
        },
        dataBrowser: [
            [navigator.userAgent, "Chrome", "Chrome"],
            [navigator.userAgent, "OmniWeb", "OmniWeb", "OmniWeb/"],
            [navigator.vendor, "Apple", "Safari", "Version"],
            [false, "Opera", "Version", false, window.opera],
            [navigator.vendor, "iCab", "iCab"],
            [navigator.vendor, "KDE", "Konqueror"],
            [navigator.userAgent, "Firefox", "Firefox"],
            [navigator.vendor, "Camino", "Camino"],
            [navigator.userAgent, "Netscape", "Netscape"], // for newer Netscapes (6+)
            [navigator.userAgent, "MSIE", "IE", "MSIE"],
            [navigator.userAgent, "Gecko", "Mozilla", "rv"],
            [navigator.userAgent, "Mozilla", "Netscape", "Mozilla"] // for older Netscapes (4-)
        ],
        dataOS: [
            [navigator.platform, "Win", "Windows"],
            [navigator.platform, "Mac", "Mac"],
            [navigator.userAgent, "iPhone", "iPhone/iPod"],
            [navigator.platform, "Linux", "Linux"]
        ]

    };
    RIABuilder.BrowserDetect.init();
})();