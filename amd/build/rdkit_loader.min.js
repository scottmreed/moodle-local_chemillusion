// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Lazy, single-shot loader for the bundled RDKit.js/WASM build.
 *
 * RDKit is only fetched when a ChemIllusion study tool actually needs it, never
 * on every Moodle page. Initialisation is cached and times out gracefully so a
 * failure degrades to the Phase 1A text/PubChem experience.
 *
 * @module     local_chemillusion/rdkit_loader
 * @copyright  2026 MolLogic / Scott Reed
 * @license    GPL-3.0-or-later
 */
define([], function() {

    'use strict';

    var cfg = {};
    var instancePromise = null;

    /**
     * Inject the RDKit loader script once.
     * @param {String} url
     * @return {Promise}
     */
    var loadScript = function(url) {
        return new Promise(function(resolve, reject) {
            if (window.initRDKitModule) {
                resolve();
                return;
            }
            var script = document.createElement('script');
            script.src = url;
            script.async = true;
            script.onload = function() {
                resolve();
            };
            script.onerror = function() {
                reject(new Error('Failed to load RDKit script.'));
            };
            document.head.appendChild(script);
        });
    };

    return {
        /**
         * Provide asset URLs before first use.
         * @param {Object} options rdkitJsUrl, rdkitWasmUrl
         */
        configure: function(options) {
            cfg = options || {};
        },

        /**
         * Resolve to an initialised RDKit module (cached).
         * @param {Object} options Optional override of asset URLs.
         * @return {Promise}
         */
        load: function(options) {
            if (options) {
                cfg = options;
            }
            if (instancePromise) {
                return instancePromise;
            }
            if (!cfg.rdkitJsUrl) {
                return Promise.reject(new Error('RDKit is not configured.'));
            }

            var init = loadScript(cfg.rdkitJsUrl).then(function() {
                if (typeof window.initRDKitModule !== 'function') {
                    throw new Error('initRDKitModule unavailable.');
                }
                return window.initRDKitModule({
                    locateFile: function() {
                        return cfg.rdkitWasmUrl;
                    }
                });
            }).then(function(rdkit) {
                window.RDKitModule = rdkit;
                return rdkit;
            });

            var timeout = new Promise(function(resolve, reject) {
                setTimeout(function() {
                    reject(new Error('RDKit initialisation timed out.'));
                }, 15000);
            });

            instancePromise = Promise.race([init, timeout]);
            return instancePromise;
        }
    };
});
