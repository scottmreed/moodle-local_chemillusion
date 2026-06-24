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
 * Client-side accessible summary generator.
 *
 * Mirrors the logic in classes/output/card_accessibility_summary.php so
 * that preview summaries update instantly without a round-trip. The PHP
 * class is authoritative for persisted summaries.
 *
 * @module     local_chemillusion/card_accessibility
 * @copyright  2026 MolLogic / Scott Reed
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define([], function() {

    'use strict';

    /**
     * Generate a plain-text accessible summary for a card.
     *
     * @param {string} cardType
     * @param {string} frontjson JSON string.
     * @param {string} backjson JSON string.
     * @return {string}
     */
    function generateSummary(cardType, frontjson, backjson) {
        var front = {};
        var back = {};
        try {
            front = JSON.parse(frontjson || '{}');
        } catch (_) {
            // Ignore invalid JSON.
        }
        try {
            back = JSON.parse(backjson || '{}');
        } catch (_) {
            // Ignore invalid JSON.
        }

        switch (cardType) {
            case 'newman_projection':
                return _newmanSummary(front);
            case 'reaction_coordinate':
                return _rcdSummary(front);
            case 'orbital_hybridization':
                return _orbitalSummary(front);
            case 'reagent_card':
                return _reagentSummary(front, back);
            default:
                return _moleculeSummary(front, back);
        }
    }

    /**
     * Update aria-label and visible summary element in the DOM.
     *
     * @param {string} cardType
     * @param {string} frontjson
     * @param {string} backjson
     */
    function update(cardType, frontjson, backjson) {
        var text = generateSummary(cardType, frontjson, backjson);
        var sumEl = document.querySelector('.local-chemillusion-accessible-summary');
        var faceEl = document.querySelector('.local-chemillusion-card-face');

        if (sumEl) {
            sumEl.textContent = text;
        }
        if (faceEl) {
            faceEl.setAttribute('aria-label', text);
        }
    }

    /**
     * Build a Newman projection accessible summary.
     *
     * @param {Object} front Decoded front card data.
     * @return {string}
     */
    function _newmanSummary(front) {
        var bond = front.bond_label || 'C–C';
        var flist = (front.front || ['?', '?', '?']).join(', ');
        var blist = (front.back || ['?', '?', '?']).join(', ');
        var rot = front.rotation_degrees !== undefined ? front.rotation_degrees : 0;
        var conf = front.conformation ? ' Conformation: ' + front.conformation + '.' : '';
        return 'Newman projection looking down the ' + bond + ' bond.'
            + ' Front substituents: ' + flist + '.'
            + ' Back substituents: ' + blist + '.'
            + ' Back carbon rotated ' + rot + '°.' + conf;
    }

    /**
     * Build a reaction coordinate diagram accessible summary.
     *
     * @param {Object} front Decoded front card data.
     * @return {string}
     */
    function _rcdSummary(front) {
        var title = front.title || 'Reaction';
        var points = front.points || [];
        var tsCount = points.filter(function(p) {
            return (p.id || '').toLowerCase().indexOf('ts') === 0;
        }).length;
        var intCount = points.filter(function(p) {
            return (p.id || '').toLowerCase().indexOf('int') === 0;
        }).length;
        var first = points[0];
        var last = points[points.length - 1];
        var direction = '';
        if (first && last) {
            direction = last.y > first.y
                ? 'The overall reaction is exergonic.'
                : 'The overall reaction is endergonic.';
        }
        return 'Reaction coordinate diagram: ' + title + '. '
            + tsCount + ' transition state(s) and ' + intCount + ' intermediate(s). '
            + direction;
    }

    /**
     * Build an orbital hybridization accessible summary.
     *
     * @param {Object} front Decoded front card data.
     * @return {string}
     */
    function _orbitalSummary(front) {
        var label = front.label || front.smiles || 'molecule';
        var hyb = front.hybridization || '';
        var topic = front.topic || '';
        var parts = ['Orbital diagram for ' + label + '.'];
        if (hyb) {
            parts.push('Hybridization: ' + hyb + '.');
        }
        if (topic) {
            parts.push(topic.charAt(0).toUpperCase() + topic.slice(1) + '.');
        }
        return parts.join(' ');
    }

    /**
     * Build a molecule card accessible summary.
     *
     * @param {Object} front Decoded front card data.
     * @param {Object} back Decoded back card data.
     * @return {string}
     */
    function _moleculeSummary(front, back) {
        var name = front.name || back.name || '';
        var smiles = front.smiles || back.canonical_smiles || '';
        var formula = back.formula || '';
        var groups = back.functional_groups || [];
        var parts = [];
        if (name) {
            parts.push('Molecule: ' + name + '.');
        }
        if (formula) {
            parts.push('Formula: ' + formula + '.');
        }
        if (smiles) {
            parts.push('SMILES: ' + smiles + '.');
        }
        if (groups.length) {
            parts.push('Functional groups: ' + groups.join(', ') + '.');
        }
        return parts.length ? parts.join(' ') : 'Chemical structure diagram.';
    }

    /**
     * Build a reagent card accessible summary.
     *
     * @param {Object} front Decoded front card data.
     * @param {Object} back Decoded back card data.
     * @return {string}
     */
    function _reagentSummary(front, back) {
        var acronym = front.acronym || '';
        var name = back.full_name || front.full_name || '';
        var role = back.role || front.role || '';
        var parts = [];
        if (acronym) {
            parts.push('Reagent: ' + acronym + '.');
        }
        if (name) {
            parts.push('Full name: ' + name + '.');
        }
        if (role) {
            parts.push('Role: ' + role + '.');
        }
        return parts.length ? parts.join(' ') : 'Reagent card.';
    }

    return {generateSummary: generateSummary, update: update};
});
