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
 * Orbital and hybridization card renderer.
 *
 * Two modes:
 *  1. Curated mode — inlines a pre-authored SVG asset from pix/orbitals/.
 *     Used when the card data includes a curated template id.
 *  2. Rule-based mode — uses conservative local rules to place simple
 *     p-orbital lobe shapes around selected atoms from RDKit 2D coordinates.
 *  3. Fallback — renders a CTA when neither curated nor rule-based applies.
 *
 * @module     local_chemillusion/orbital_overlay_renderer
 * @copyright  2026 MolLogic / Scott Reed
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['core/ajax'], function(Ajax) {

    'use strict';

    /**
     * Render into containerEl based on card data.
     *
     * @param {Object}      data         Decoded frontjson.
     * @param {HTMLElement} containerEl  DOM element to render into.
     */
    function render(data, containerEl) {
        if (!containerEl) {
            return;
        }

        // Curated mode: if template_id is set, fetch the SVG asset inline.
        if (data.template_id) {
            _renderCurated(data, containerEl);
            return;
        }

        // Rule-based mode: use SMILES + atom_idx.
        if (data.smiles) {
            _renderRuleBased(data, containerEl);
            return;
        }

        _renderFallback(containerEl, data.chemillusion_url || '');
    }

    /**
     * Render a hybridization label and explanation (no SVG required).
     *
     * @param {string} smiles
     * @param {number} atomIdx
     * @return {Object}  { hybridization, explanation }
     */
    function renderHybridization(smiles, atomIdx) {
        var hyb = _classifyHybridization(smiles, atomIdx);
        var exp = _hybridizationExplanation(hyb, smiles);
        return { hybridization: hyb, explanation: exp };
    }

    // ---- Private helpers ---------------------------------------------------

    function _renderCurated(data, el) {
        // Fetch the SVG asset via AJAX from a static Moodle URL.
        var url = M.cfg.wwwroot + '/local/chemillusion/pix/orbitals/' + data.template_id + '.svg';
        fetch(url)
            .then(function(resp) {
                if (!resp.ok) { throw new Error('asset not found'); }
                return resp.text();
            })
            .then(function(svg) {
                el.innerHTML = '<div class="local-chemillusion-orbital-card">' + svg + '</div>';
                if (data.description) {
                    el.innerHTML += '<p class="mt-2 text-muted small">' + _esc(data.description) + '</p>';
                }
            })
            .catch(function() {
                _renderFallback(el, data.chemillusion_url || '');
            });
    }

    function _renderRuleBased(data, el) {
        // Use RDKit to get 2D coordinates, then place p-orbital lobes.
        require(['local_chemillusion/rdkit_molecule_renderer'], function(Renderer) {
            Renderer.renderSMILES(data.smiles, el).then(function() {
                // After molecule SVG is injected, attempt to add p-orbital lobes.
                var svgEl = el.querySelector('svg');
                if (!svgEl) {
                    _renderFallback(el, data.chemillusion_url || '');
                    return;
                }
                var hyb = _classifyHybridization(data.smiles, data.atom_idx);
                if (!hyb) {
                    _renderFallback(el, data.chemillusion_url || '');
                    return;
                }
                // Add simple p-orbital lobe overlay for sp/sp2 atoms.
                if (hyb === 'sp' || hyb === 'sp2') {
                    _addPOrbitalLobes(svgEl, data.atom_idx || 0, hyb);
                }
                // Hybridization label badge.
                var badge = document.createElement('div');
                badge.className = 'badge bg-info text-dark mt-2';
                badge.textContent = hyb + ' hybridisation';
                el.appendChild(badge);
            }).catch(function() {
                _renderFallback(el, data.chemillusion_url || '');
            });
        });
    }

    function _addPOrbitalLobes(svgEl, atomIdx, hybridization) {
        // Attempt to find atom position from RDKit-rendered SVG atom data.
        // RDKit SVG includes data-atom-idx attributes on atom groups.
        var atomEl = svgEl.querySelector('[data-atom-idx="' + atomIdx + '"]');
        var x = parseFloat(svgEl.getAttribute('width') || 200) / 2;
        var y = parseFloat(svgEl.getAttribute('height') || 200) / 2;

        if (atomEl) {
            var bbox = atomEl.getBoundingClientRect();
            var svgBbox = svgEl.getBoundingClientRect();
            x = bbox.left - svgBbox.left + bbox.width / 2;
            y = bbox.top  - svgBbox.top  + bbox.height / 2;
        }

        // Draw simple p-orbital lobe ellipses perpendicular to molecular plane.
        var ns  = 'http://www.w3.org/2000/svg';
        var lw  = hybridization === 'sp' ? 20 : 14;
        var lh  = hybridization === 'sp' ? 36 : 28;
        var clr = '#0dcaf0';

        // Upper lobe.
        var up = document.createElementNS(ns, 'ellipse');
        up.setAttribute('cx', x);
        up.setAttribute('cy', y - lh / 2 - 2);
        up.setAttribute('rx', lw / 2);
        up.setAttribute('ry', lh / 2);
        up.setAttribute('fill', clr);
        up.setAttribute('fill-opacity', '0.45');
        up.setAttribute('stroke', '#0a8fa8');
        up.setAttribute('stroke-width', '1');
        svgEl.appendChild(up);

        // Lower lobe.
        var dn = document.createElementNS(ns, 'ellipse');
        dn.setAttribute('cx', x);
        dn.setAttribute('cy', y + lh / 2 + 2);
        dn.setAttribute('rx', lw / 2);
        dn.setAttribute('ry', lh / 2);
        dn.setAttribute('fill', '#ff922b');
        dn.setAttribute('fill-opacity', '0.45');
        dn.setAttribute('stroke', '#c0561e');
        dn.setAttribute('stroke-width', '1');
        svgEl.appendChild(dn);
    }

    function _classifyHybridization(smiles, atomIdx) {
        // Conservative local rules — cannot compute without RDKit context.
        // Return a string hint based on trivial SMILES patterns.
        if (!smiles) { return null; }
        var s = smiles.toLowerCase();
        if (s.indexOf('c#') !== -1 || s.indexOf('#c') !== -1) { return 'sp'; }
        if (s.indexOf('c=c') !== -1 || s.indexOf('c=o') !== -1 || s.indexOf('c=n') !== -1) { return 'sp2'; }
        if (s.match(/c1[c=]c[c=]c[c=]1/)) { return 'sp2'; }
        return 'sp3';
    }

    function _hybridizationExplanation(hyb, smiles) {
        var explanations = {
            'sp':  'sp hybridisation: two sp orbitals form a linear σ framework; two p orbitals form perpendicular π bonds.',
            'sp2': 'sp² hybridisation: three sp² orbitals form a trigonal planar σ framework; one p orbital is available for π bonding.',
            'sp3': 'sp³ hybridisation: four sp³ orbitals form a tetrahedral σ framework; no p orbital available for π bonding.',
        };
        return explanations[hyb] || 'Hybridisation could not be determined from local rules alone.';
    }

    function _renderFallback(el, chemillusionUrl) {
        var ctaHref = chemillusionUrl || (M.cfg.wwwroot + '/local/chemillusion/link.php');
        el.innerHTML = '<div class="local-chemillusion-orbital-fallback">'
            + '<p>' + M.util.get_string('orbital_no_overlay', 'local_chemillusion') + '</p>'
            + '<a class="btn btn-sm btn-outline-primary" href="' + _esc(ctaHref) + '">'
            + M.util.get_string('orbital_cta', 'local_chemillusion')
            + '</a>'
            + '</div>';
    }

    function _esc(s) {
        return String(s)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    return { render: render, renderHybridization: renderHybridization };
});
