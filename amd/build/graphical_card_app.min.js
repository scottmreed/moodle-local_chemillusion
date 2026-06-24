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
     * Toggle editor form sections based on card type.
     *
     * @param {string} selectedType
     * @param {Object} sections Section id to card-type map.
     */
    function updateSections(selectedType, sections) {
        Object.keys(sections).forEach(function(sectionId) {
            var el = document.getElementById('id_' + sectionId);
            if (!el) {
                return;
            }
            var relevantTypes = sections[sectionId];
            el.style.display = relevantTypes.indexOf(selectedType) !== -1 ? '' : 'none';
        });
    }

    /**
     * Init the card editor (card_edit.php).
     * Progressively enhances the PHP form with type-dependent section toggling.
     *
     * @param {Object} opts Editor options from PHP.
     */
    function initEditor(opts) {
        opts = opts || {};
        var typeSelect = document.querySelector('[name="cardtype"]');
        if (!typeSelect) {
            return;
        }

        /* eslint-disable camelcase */
        var sections = {
            molecule_section: ['molecule_identity', 'functional_group_highlight', 'functional_group_list',
                'smiles_to_structure', 'structure_to_smiles', 'accessibility_card'],
            newman_section: ['newman_projection'],
            rcd_section: ['reaction_coordinate'],
            orbital_section: ['orbital_hybridization'],
            reagent_section: ['reagent_card'],
        };
        /* eslint-enable camelcase */

        updateSections(typeSelect.value, sections);
        typeSelect.addEventListener('change', function() {
            updateSections(typeSelect.value, sections);
        });

        _initFocusedPreview(opts);
        _tryInitNewmanPreview();
    }

    /**
     * Build molecule preview data from form values.
     *
     * @param {Object} values
     * @return {Object}
     */
    function buildMoleculePreviewData(values) {
        return {smiles: values.smiles || ''};
    }

    /**
     * Build Newman preview data from form values.
     *
     * @param {Object} values
     * @return {Object}
     */
    function buildNewmanPreviewData(values) {
        var data = {
            front: [
                values.newman_front_a || 'H',
                values.newman_front_b || 'H',
                values.newman_front_c || 'H',
            ],
            back: [
                values.newman_back_a || 'H',
                values.newman_back_b || 'H',
                values.newman_back_c || 'H',
            ],
        };
        data.rotation_degrees = parseInt(values.newman_rotation || '0', 10); // eslint-disable-line camelcase
        data.show_energy_hint = Boolean(values.newman_show_energy); // eslint-disable-line camelcase
        return data;
    }

    /**
     * Build reaction coordinate preview data from form values.
     *
     * @param {Object} values
     * @param {Object} presets
     * @return {Object}
     */
    function buildReactionPreviewData(values, presets) {
        var reaction = Object.assign({}, presets[values.rcd_template] || {});
        try {
            var points = JSON.parse(values.rcd_points_json || '[]');
            if (Array.isArray(points) && points.length >= 2) {
                reaction.points = points;
            }
        } catch (error) {
            // Keep the selected preset visible while advanced JSON is incomplete.
        }
        return reaction;
    }

    /**
     * Build orbital preview data from form values.
     *
     * @param {Object} values
     * @param {Object} presets
     * @return {Object}
     */
    function buildOrbitalPreviewData(values, presets) {
        var orbital = Object.assign({}, presets[values.orbital_template] || {});
        orbital.smiles = values.orbital_smiles || orbital.smiles || '';
        orbital.atom_idx = parseInt(values.orbital_atom_idx || '0', 10); // eslint-disable-line camelcase
        return orbital;
    }

    /**
     * Convert form values into the data shape expected by a diagram renderer.
     *
     * @param {string} toolid
     * @param {Object} values
     * @param {Object} presets
     * @return {Object}
     */
    function buildPreviewData(toolid, values, presets) {
        presets = presets || {};
        if (toolid === 'molecule') {
            return buildMoleculePreviewData(values);
        }
        if (toolid === 'newman') {
            return buildNewmanPreviewData(values);
        }
        if (toolid === 'reaction') {
            return buildReactionPreviewData(values, presets);
        }
        if (toolid === 'orbital') {
            return buildOrbitalPreviewData(values, presets);
        }
        return {};
    }

    /**
     * Read current preview form field values for a tool.
     *
     * @param {string} toolid
     * @param {Object} fieldNames
     * @return {Object}
     */
    function readPreviewValues(toolid, fieldNames) {
        var values = {};
        (fieldNames[toolid] || []).forEach(function(name) {
            var field = document.querySelector('[name="' + name + '"]');
            if (field) {
                values[name] = field.type === 'checkbox' ? field.checked : field.value;
            }
        });
        return values;
    }

    /**
     * Render the diagram preview canvas for the active tool.
     *
     * @param {string} toolid
     * @param {HTMLElement} canvas
     * @param {Object} presets
     * @param {Object} fieldNames
     */
    function renderDiagramPreview(toolid, canvas, presets, fieldNames) {
        var data = buildPreviewData(toolid, readPreviewValues(toolid, fieldNames), presets);
        if (toolid === 'molecule') {
            if (!data.smiles) {
                canvas.innerHTML = '';
                return;
            }
            require(['local_chemillusion/rdkit_molecule_renderer'], function(Renderer) {
                Renderer.render(canvas, data.smiles);
            });
            return;
        }
        if (toolid === 'newman') {
            require(['local_chemillusion/newman_projection'], function(Newman) {
                canvas.innerHTML = Newman.render(data);
            });
            return;
        }
        if (toolid === 'reaction') {
            require(['local_chemillusion/reaction_coordinate_diagram'], function(RCD) {
                canvas.innerHTML = RCD.render(data);
            });
            return;
        }
        if (toolid === 'orbital') {
            require(['local_chemillusion/orbital_overlay_renderer'], function(Orbital) {
                Orbital.render(data, canvas);
            });
        }
    }

    /**
     * Set a form field value by name.
     *
     * @param {string} name
     * @param {*} value
     */
    function setPreviewField(name, value) {
        var field = document.querySelector('[name="' + name + '"]');
        if (!field) {
            return;
        }
        if (field.type === 'checkbox') {
            field.checked = Boolean(value);
        } else {
            field.value = value === undefined || value === null ? '' : value;
        }
    }

    /**
     * Apply a preset to the editor form fields.
     *
     * @param {string} toolid
     * @param {string} presetid
     * @param {Object} presets
     * @param {Function} scheduleRender
     */
    function applyEditorPreset(toolid, presetid, presets, scheduleRender) {
        var preset = presets[presetid];
        if (!preset) {
            scheduleRender();
            return;
        }
        if (toolid === 'newman') {
            ['a', 'b', 'c'].forEach(function(key, index) {
                setPreviewField('newman_front_' + key, preset.front[index]);
                setPreviewField('newman_back_' + key, preset.back[index]);
            });
            setPreviewField('newman_rotation', preset.rotation_degrees);
            setPreviewField('newman_show_energy', preset.show_energy_hint);
        } else if (toolid === 'reaction') {
            setPreviewField('rcd_points_json', JSON.stringify(preset.points || []));
        } else if (toolid === 'orbital') {
            setPreviewField('orbital_smiles', preset.smiles || '');
            setPreviewField('orbital_atom_idx', preset.atom_idx || 0);
        }
        setPreviewField('name', preset.title || '');
        setPreviewField('teacher_note', preset.teacher_note || '');
        scheduleRender();
    }

    /**
     * Resolve the preset select field name for a tool.
     *
     * @param {string} toolid
     * @return {string}
     */
    function presetFieldNameForTool(toolid) {
        if (toolid === 'newman') {
            return 'newman_preset';
        }
        if (toolid === 'reaction') {
            return 'rcd_template';
        }
        return 'orbital_template';
    }

    /**
     * Wire up live diagram preview on the focused editor page.
     *
     * @param {Object} opts
     */
    function _initFocusedPreview(opts) {
        var preview = document.querySelector('[data-region="diagram-preview"]');
        var canvas = document.querySelector('[data-region="diagram-preview-canvas"]');
        var toolid = opts.toolid || '';
        var presets = opts.presets || {};
        if (!preview || !canvas || !toolid) {
            return;
        }

        var fieldNames = {
            molecule: ['smiles'],
            newman: [
                'newman_preset',
                'newman_front_a', 'newman_front_b', 'newman_front_c',
                'newman_back_a', 'newman_back_b', 'newman_back_c',
                'newman_rotation', 'newman_show_energy',
            ],
            reaction: ['rcd_template', 'rcd_points_json'],
            orbital: ['orbital_template', 'orbital_smiles', 'orbital_atom_idx'],
        };
        var renderTimer = null;

        var scheduleRender = function() {
            window.clearTimeout(renderTimer);
            renderTimer = window.setTimeout(function() {
                renderDiagramPreview(toolid, canvas, presets, fieldNames);
            }, toolid === 'molecule' ? 180 : 0);
        };

        (fieldNames[toolid] || []).forEach(function(name) {
            var field = document.querySelector('[name="' + name + '"]');
            if (!field) {
                return;
            }
            var eventName = field.tagName === 'SELECT' ? 'change' : 'input';
            field.addEventListener(eventName, scheduleRender);
        });

        var presetField = document.querySelector('[name="' + presetFieldNameForTool(toolid) + '"]');
        if (presetField && toolid !== 'molecule') {
            presetField.addEventListener('change', function() {
                applyEditorPreset(toolid, presetField.value, presets, scheduleRender);
            });
        }

        renderDiagramPreview(toolid, canvas, presets, fieldNames);
    }

    /**
     * Init the card viewer (card_view.php).
     *
     * @param {Object} opts { cardid }
     */
    function initView(opts) {
        var cardid = opts.cardid;
        if (!cardid) {
            return;
        }

        var frontEl = document.querySelector('[data-region="card-front"]');
        var backEl = document.querySelector('[data-region="card-back"]');
        var flipBtn = document.querySelector('[data-action="flip-card"]');
        var shown = false;

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

        _initExportToolbar(cardid);

        Ajax.call([{
            methodname: 'local_chemillusion_get_graphical_card_preview',
            args: {cardid: cardid},
        }])[0].then(function(card) {
            _renderCardType(card);
            CardAccessibility.update(card.cardtype, card.frontjson, card.backjson);
            return card;
        }).catch(function() {
            // Silently fall back to PHP-rendered content.
            return null;
        });
    }

    /**
     * Wire export toolbar buttons for SVG, PNG, and clipboard actions.
     *
     * @param {number} cardid
     */
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
                var svgEl = document.querySelector('.local-chemillusion-card-face svg');
                var summaryEl = document.querySelector('.local-chemillusion-accessible-summary');
                var summary = summaryEl ? summaryEl.textContent : '';
                if (svgEl) {
                    SvgExporter.copyMoodleSnippet(svgEl, summary);
                }
            });
        });
    }

    /**
     * Render type-specific diagram content into the card viewer.
     *
     * @param {Object} card
     */
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
                if (frontjson.smiles) {
                    require(['local_chemillusion/rdkit_molecule_renderer'], function(Renderer) {
                        Renderer.render(region, frontjson.smiles);
                    });
                }
        }
    }

    /**
     * Read a Newman form field value with a default.
     *
     * @param {string} name
     * @param {string} fallback
     * @return {string}
     */
    function _newmanFieldValue(name, fallback) {
        var field = document.querySelector('[name="' + name + '"]');
        return field ? field.value : fallback;
    }

    /**
     * Wire live Newman preview on the legacy editor form.
     */
    function _tryInitNewmanPreview() {
        var newmanFields = document.querySelectorAll('[name^="newman_"]');
        if (!newmanFields.length) {
            return;
        }
        var previewEl = document.getElementById('local-chemillusion-newman-preview');
        if (!previewEl) {
            return;
        }

        /**
         * Refresh the Newman preview SVG from current form values.
         */
        var updatePreview = function() {
            require(['local_chemillusion/newman_projection'], function(Newman) {
                var front = [
                    _newmanFieldValue('newman_front_a', 'H'),
                    _newmanFieldValue('newman_front_b', 'H'),
                    _newmanFieldValue('newman_front_c', 'H'),
                ];
                var back = [
                    _newmanFieldValue('newman_back_a', 'H'),
                    _newmanFieldValue('newman_back_b', 'H'),
                    _newmanFieldValue('newman_back_c', 'H'),
                ];
                var rot = parseInt(_newmanFieldValue('newman_rotation', '0'), 10);
                var newmanData = {front: front, back: back};
                newmanData.rotation_degrees = rot; // eslint-disable-line camelcase
                var svg = Newman.render(newmanData);
                previewEl.innerHTML = svg;
            });
        };

        newmanFields.forEach(function(f) {
            f.addEventListener('input', updatePreview);
        });
        updatePreview();
    }

    return {
        init: init,
        initEditor: initEditor,
        initView: initView,
        buildPreviewData: buildPreviewData,
    };
});
