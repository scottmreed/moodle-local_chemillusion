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
 * Live molecule lookup, result rendering, and Phase 1B RDKit enhancement.
 *
 * @module     local_chemillusion/molecule_lookup
 * @copyright  2026 MolLogic / Scott Reed
 * @license    GPL-3.0-or-later
 */
define([
    'core/ajax',
    'core/templates',
    'core/notification',
    'core/str'
], function(Ajax, Templates, Notification, Str) {

    'use strict';

    var config = {};

    /**
     * Call the lookup web service.
     * @param {String} query
     * @param {String|null} forceType Optional type override for retry.
     * @return {Promise}
     */
    var lookup = function(query, forceType) {
        var args = {query: query};
        if (forceType) {
            args.force_type = forceType; // eslint-disable-line camelcase
        }
        return Ajax.call([{
            methodname: 'local_chemillusion_lookup_molecule',
            args: args
        }])[0];
    };

    /**
     * Build a server-signed launch URL for a CTA.
     * @param {String} cta
     * @param {Object} data
     * @return {String}
     */
    var launchUrl = function(cta, data) {
        var params = [
            'cta=' + encodeURIComponent(cta),
            'surface=molecule_lookup',
            'sesskey=' + encodeURIComponent(config.sesskey || ''),
            'name=' + encodeURIComponent(data.name || ''),
            'smiles=' + encodeURIComponent(data.canonical_smiles || ''),
            'cid=' + encodeURIComponent(data.cid || ''),
            'inchikey=' + encodeURIComponent(data.inchikey || '')
        ];
        return config.launchUrl + '?' + params.join('&');
    };

    /**
     * Render a resolved molecule and optionally enhance with RDKit.
     * @param {Object} data
     * @return {Promise}
     */
    var renderResult = function(data) {
        /* eslint-disable camelcase */
        var context = {
            name: data.name,
            cid: data.cid,
            formula: data.formula,
            mw: data.mw,
            canonical_smiles: data.canonical_smiles,
            isomeric_smiles: data.isomeric_smiles,
            inchikey: data.inchikey,
            pubchem_url: data.pubchem_url,
            has_pubchem: Boolean(data.pubchem_url),
            has_smiles: Boolean(data.canonical_smiles),
            rdkit_enabled: Boolean(config.rdkitEnabled),
            open_url: launchUrl('continue', data),
            visual_enabled: Boolean(config.visualEnabled),
            visual_url: launchUrl('visual_card', data),
            visual_blurb: ''
        };
        /* eslint-enable camelcase */
        return Templates.render('local_chemillusion/molecule_result_card', context)
            .then(function(html) {
                var region = document.getElementById('local-chemillusion-results');
                region.innerHTML = html;
                if (config.rdkitEnabled && data.canonical_smiles) {
                    enhance(region, data.canonical_smiles);
                }
                return html;
            });
    };

    /**
     * Lazy-load RDKit and render structure + functional groups.
     * @param {Element} region
     * @param {String} smiles
     */
    var enhance = function(region, smiles) {
        require([
            'local_chemillusion/rdkit_molecule_renderer',
            'local_chemillusion/functional_group_matcher'
        ], function(Renderer, Matcher) {
            var svg = region.querySelector('[data-region="structure-svg"]');
            if (svg) {
                Renderer.render(svg, smiles);
            }
            var fg = region.querySelector('[data-region="functional-groups"]');
            if (fg) {
                Matcher.detect(smiles, config.smarts || []).then(function(groups) {
                    return renderGroups(fg, groups, smiles, Renderer);
                }).catch(function() {
                    return null;
                });
            }
        });
    };

    /**
     * Render functional-group badges with highlight handlers.
     * @param {Element} fg
     * @param {Array} groups
     * @param {String} smiles
     * @param {Object} Renderer
     * @return {Promise}
     */
    var renderGroups = function(fg, groups, smiles, Renderer) {
        fg.innerHTML = '';
        var promises = groups.map(function(g) {
            return Templates.render('local_chemillusion/functional_group_badge', {
                id: g.id, label: g.label, summary: g.summary || ''
            }).then(function(html) {
                var wrap = document.createElement('span');
                wrap.innerHTML = html;
                var btn = wrap.querySelector('[data-action="highlight-group"]');
                if (btn) {
                    btn.addEventListener('click', function() {
                        var card = fg.closest('[data-region="molecule-card"]');
                        var target = card && card.querySelector('[data-region="structure-svg"]');
                        if (target) {
                            Renderer.highlight(target, smiles, g.smarts);
                        }
                    });
                }
                fg.appendChild(wrap);
                return null;
            });
        });
        return Promise.all(promises);
    };

    /**
     * Render lookup error UI with optional retry buttons.
     * @param {Object} data Error payload from the web service.
     * @return {Promise}
     */
    var renderError = function(data) {
        return Str.get_string('error_' + data.error, 'local_chemillusion').then(function(msg) {
            var html = '<div class="alert alert-warning" role="alert">' + msg;

            if (data.error_note) {
                html += '<br/><em>' + data.error_note + '</em>';
            }

            if (data.alt_types && data.alt_types.length > 0) {
                html += '<div class="mt-2">';
                data.alt_types.forEach(function(altType) {
                    html += '<button type="button" class="btn btn-sm btn-outline-secondary" ' +
                        'data-action="retry-lookup" data-type="' + altType + '">' +
                        'Try searching by ' + altType + '</button> ';
                });
                html += '</div>';
            }

            html += '</div>';
            var region = document.getElementById('local-chemillusion-results');
            region.innerHTML = html;

            region.querySelectorAll('[data-action="retry-lookup"]').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var retryType = btn.getAttribute('data-type');
                    onSubmit(null, retryType);
                });
            });

            return null;
        });
    };

    /**
     * Submit handler for the lookup form (with optional forceType override).
     * @param {Event|null} e
     * @param {String|null} forceType Optional type override for retry.
     */
    var onSubmit = function(e, forceType) {
        if (e && e.preventDefault) {
            e.preventDefault();
        }
        var input = document.getElementById('id_query');
        var query = input ? input.value.trim() : '';
        if (!query) {
            return;
        }

        lookup(query, forceType).then(function(data) {
            if (data.status === 'ok') {
                return renderResult(data);
            }
            return renderError(data);
        }).catch(Notification.exception);
    };

    return {
        /**
         * Initialise live lookup.
         * @param {Object} cfg
         */
        init: function(cfg) {
            config = cfg || {};
            if (config.rdkitEnabled) {
                require(['local_chemillusion/rdkit_loader'], function(Loader) {
                    Loader.configure({
                        rdkitJsUrl: config.rdkitJsUrl,
                        rdkitWasmUrl: config.rdkitWasmUrl
                    });
                });
            }
            var input = document.getElementById('id_query');
            if (input && input.form) {
                input.form.addEventListener('submit', onSubmit);
            }
        }
    };
});
