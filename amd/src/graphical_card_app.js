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
 * Graphical card application entry point.
 *
 * Initialises the browse page, editor, and viewer. Delegates rendering
 * to type-specific AMD modules (newman_projection, reaction_coordinate_diagram,
 * orbital_overlay_renderer) and handles keyboard navigation, card flip,
 * and SVG export toolbar.
 *
 * @module     local_chemillusion/graphical_card_app
 * @copyright  2026 MolLogic / Scott Reed
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define([
    'core/ajax',
    'local_chemillusion/svg_exporter',
    'local_chemillusion/card_accessibility',
], function(Ajax, SvgExporter, CardAccessibility) {

    'use strict';

    /**
     * Init the browse page (graphical.php).
     */
    function init() {
        // Nothing to init beyond PHP-rendered page for now.
        // Future: filter/search cards client-side.
    }

    /**
     * Init the card editor (card_edit.php).
     * Progressively enhances the PHP form with type-dependent section toggling.
     */
    function initEditor() {
        var typeSelect = document.querySelector('[name="cardtype"]');
        if (!typeSelect) {
            return;
        }

        var sections = {
            molecule_section:  ['molecule_identity', 'functional_group_highlight', 'functional_group_list',
                                'smiles_to_structure', 'structure_to_smiles', 'accessibility_card'],
            newman_section:    ['newman_projection'],
            rcd_section:       ['reaction_coordinate'],
            orbital_section:   ['orbital_hybridization'],
            reagent_section:   ['reagent_card'],
        };

        function updateSections(selectedType) {
            Object.keys(sections).forEach(function(sectionId) {
                var el = document.getElementById('id_' + sectionId);
                if (!el) {
                    return;
                }
                var relevantTypes = sections[sectionId];
                el.style.display = relevantTypes.indexOf(selectedType) !== -1 ? '' : 'none';
            });
        }

        updateSections(typeSelect.value);
        typeSelect.addEventListener('change', function() {
            updateSections(this.value);
        });

        // Newman live preview wired up when renderer is loaded.
        _tryInitNewmanPreview();
    }

    /**
     * Init the card viewer (card_view.php).
     *
     * @param {Object} opts  { cardid }
     */
    function initView(opts) {
        var cardid = opts.cardid;
        if (!cardid) {
            return;
        }

        var frontEl = document.querySelector('[data-region="card-front"]');
        var backEl  = document.querySelector('[data-region="card-back"]');
        var flipBtn = document.querySelector('[data-action="flip-card"]');
        var shown   = false;

        if (flipBtn) {
            flipBtn.addEventListener('click', function() {
                shown = !shown;
                if (frontEl) {
                    frontEl.hidden = shown;
                }
                if (backEl) {
                    backEl.hidden = !shown;
                }
                flipBtn.setAttribute('aria-expanded', shown ? 'true' : 'false');
                flipBtn.textContent = shown
                    ? M.util.get_string('card_hide_answer', 'local_chemillusion')
                    : M.util.get_string('card_show_answer', 'local_chemillusion');
            });
        }

        // Keyboard: space/enter = flip, left = prev, right = next.
        document.addEventListener('keydown', function(e) {
            if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') {
                return;
            }
            if (e.key === ' ' || e.key === 'Enter') {
                if (flipBtn) {
                    flipBtn.click();
                }
                e.preventDefault();
            }
        });

        // Export toolbar.
        _initExportToolbar(cardid);

        // Fetch card data and render type-specific SVG.
        Ajax.call([{
            methodname: 'local_chemillusion_get_graphical_card_preview',
            args: { cardid: cardid },
        }])[0].then(function(card) {
            _renderCardType(card);
            CardAccessibility.update(card.cardtype, card.frontjson, card.backjson);
            return card;
        }).catch(function() {
            // Silently fall back to PHP-rendered content.
        });
    }

    function _initExportToolbar(cardid) {
        document.querySelectorAll('[data-action="export-svg"]').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var svgEl = document.querySelector('.local-chemillusion-card-face svg');
                if (svgEl) {
                    SvgExporter.exportSVG(svgEl, 'chemillusion-card-' + cardid + '.svg');
                }
            });
        });

        document.querySelectorAll('[data-action="export-png"]').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var svgEl = document.querySelector('.local-chemillusion-card-face svg');
                if (svgEl) {
                    SvgExporter.exportPNG(svgEl, 'chemillusion-card-' + cardid + '.png', 600, 400);
                }
            });
        });

        document.querySelectorAll('[data-action="copy-svg"]').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var svgEl = document.querySelector('.local-chemillusion-card-face svg');
                if (svgEl) {
                    SvgExporter.copySVGToClipboard(svgEl);
                }
            });
        });

        document.querySelectorAll('[data-action="copy-snippet"]').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var svgEl     = document.querySelector('.local-chemillusion-card-face svg');
                var summaryEl = document.querySelector('.local-chemillusion-accessible-summary');
                var summary   = summaryEl ? summaryEl.textContent : '';
                if (svgEl) {
                    SvgExporter.copyMoodleSnippet(svgEl, summary);
                }
            });
        });
    }

    function _renderCardType(card) {
        var frontjson = JSON.parse(card.frontjson || '{}');
        var region = document.querySelector('[data-region="card-front"]');
        if (!region) {
            return;
        }
        switch (card.cardtype) {
            case 'newman_projection':
                require(['local_chemillusion/newman_projection'], function(Newman) {
                    var svg = Newman.render(frontjson);
                    region.innerHTML = svg;
                });
                break;
            case 'reaction_coordinate':
                require(['local_chemillusion/reaction_coordinate_diagram'], function(RCD) {
                    var svg = RCD.render(frontjson);
                    region.innerHTML = svg;
                });
                break;
            case 'orbital_hybridization':
                require(['local_chemillusion/orbital_overlay_renderer'], function(Orbital) {
                    Orbital.render(frontjson, region);
                });
                break;
            default:
                // Molecule types rendered by rdkit_molecule_renderer via existing phase 1B code.
                if (frontjson.smiles) {
                    require(['local_chemillusion/rdkit_molecule_renderer'], function(Renderer) {
                        Renderer.renderSMILES(frontjson.smiles, region);
                    });
                }
        }
    }

    function _tryInitNewmanPreview() {
        var newmanFields = document.querySelectorAll('[name^="newman_"]');
        if (!newmanFields.length) {
            return;
        }
        var previewEl = document.getElementById('local-chemillusion-newman-preview');
        if (!previewEl) {
            return;
        }
        function updatePreview() {
            require(['local_chemillusion/newman_projection'], function(Newman) {
                var front = [
                    document.querySelector('[name="newman_front_a"]')?.value || 'H',
                    document.querySelector('[name="newman_front_b"]')?.value || 'H',
                    document.querySelector('[name="newman_front_c"]')?.value || 'H',
                ];
                var back = [
                    document.querySelector('[name="newman_back_a"]')?.value || 'H',
                    document.querySelector('[name="newman_back_b"]')?.value || 'H',
                    document.querySelector('[name="newman_back_c"]')?.value || 'H',
                ];
                var rot = parseInt(document.querySelector('[name="newman_rotation"]')?.value || '0', 10);
                var svg = Newman.render({ front: front, back: back, rotation_degrees: rot });
                previewEl.innerHTML = svg;
            });
        }
        newmanFields.forEach(function(f) {
            f.addEventListener('input', updatePreview);
        });
        updatePreview();
    }

    return { init: init, initEditor: initEditor, initView: initView };
});
