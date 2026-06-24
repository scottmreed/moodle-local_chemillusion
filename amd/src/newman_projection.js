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
 * Pure SVG Newman projection renderer.
 *
 * No external dependencies. Produces a self-contained SVG string from
 * a card data object: { front: [A,B,C], back: [D,E,F], rotation_degrees }.
 *
 * Front bonds are solid; back bonds are dashed (convention).
 * Front substituent angles: top (270°), bottom-right (30°), bottom-left (150°).
 * Back substituents are offset by rotationDegrees relative to front.
 *
 * @module     local_chemillusion/newman_projection
 * @copyright  2026 MolLogic / Scott Reed
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define([], function() {

    'use strict';

    var CX = 150; // SVG centre x.
    var CY = 140; // SVG centre y.
    var R = 60; // Outer circle radius.
    var BOND_LEN = 90; // Bond arm length.
    var W = 300;
    var H = 280;

    // Front substituent base angles (degrees, 0° = right / 3 o'clock, CCW).
    var FRONT_ANGLES = [270, 30, 150]; // Top, bottom-right, bottom-left.

    /**
     * Render a Newman projection SVG from card data.
     *
     * @param {Object} data { front: [A,B,C], back: [D,E,F], rotation_degrees }
     * @return {string} SVG markup string.
     */
    function render(data) {
        var front = data.front || ['H', 'H', 'H'];
        var back = data.back || ['H', 'H', 'H'];
        var rot = (data.rotation_degrees || 0);

        var parts = [];
        parts.push('<svg xmlns="http://www.w3.org/2000/svg"'
            + ' width="' + W + '" height="' + H + '"'
            + ' viewBox="0 0 ' + W + ' ' + H + '"'
            + ' role="img"'
            + ' aria-label="Newman projection"'
            + ' font-family="sans-serif" font-size="13">');

        // Back bonds (dashed, before circle so circle covers centre).
        back.forEach(function(label, i) {
            var angleDeg = FRONT_ANGLES[i] + rot;
            var pt = _polar(CX, CY, BOND_LEN, angleDeg);
            parts.push(_line(CX, CY, pt.x, pt.y, '#333', '5,4'));
            parts.push(_label(pt.x, pt.y, label, angleDeg, '#555'));
        });

        // Outer circle (back carbon).
        parts.push('<circle cx="' + CX + '" cy="' + CY + '" r="' + R
            + '" fill="white" stroke="#333" stroke-width="2"/>');

        // Front bonds (solid, over the circle).
        front.forEach(function(label, i) {
            var angleDeg = FRONT_ANGLES[i];
            var ptCircle = _polar(CX, CY, R, angleDeg);
            var ptEnd = _polar(CX, CY, BOND_LEN, angleDeg);
            parts.push(_line(ptCircle.x, ptCircle.y, ptEnd.x, ptEnd.y, '#111', ''));
            parts.push(_label(ptEnd.x, ptEnd.y, label, angleDeg, '#111'));
        });

        // Front carbon dot.
        parts.push('<circle cx="' + CX + '" cy="' + CY + '" r="5" fill="#111"/>');

        parts.push('</svg>');
        return parts.join('\n');
    }

    /**
     * Return an SVG string where the back carbon is rotated by deltaDeg from current.
     *
     * @param {Object} data
     * @param {number} deltaDeg Degrees to add (positive = clockwise in SVG space).
     * @return {string}
     */
    function rotate(data, deltaDeg) {
        var newData = Object.assign({}, data);
        newData.rotation_degrees = ((data.rotation_degrees || 0) + deltaDeg) % 360; // eslint-disable-line camelcase
        return render(newData);
    }

    /**
     * Convert polar coordinates to Cartesian.
     *
     * @param {number} cx
     * @param {number} cy
     * @param {number} r
     * @param {number} deg
     * @return {Object}
     */
    function _polar(cx, cy, r, deg) {
        var rad = deg * Math.PI / 180;
        return {x: cx + r * Math.cos(rad), y: cy + r * Math.sin(rad)};
    }

    /**
     * Build an SVG line element string.
     *
     * @param {number} x1
     * @param {number} y1
     * @param {number} x2
     * @param {number} y2
     * @param {string} stroke
     * @param {string} dasharray
     * @return {string}
     */
    function _line(x1, y1, x2, y2, stroke, dasharray) {
        var da = dasharray ? ' stroke-dasharray="' + dasharray + '"' : '';
        return '<line x1="' + _r(x1) + '" y1="' + _r(y1)
            + '" x2="' + _r(x2) + '" y2="' + _r(y2)
            + '" stroke="' + stroke + '" stroke-width="2"' + da + '/>';
    }

    /**
     * Build an SVG text label element string.
     *
     * @param {number} x
     * @param {number} y
     * @param {string} text
     * @param {number} angleDeg
     * @param {string} color
     * @return {string}
     */
    function _label(x, y, text, angleDeg, color) {
        // Offset label slightly in the direction away from centre.
        var rad = angleDeg * Math.PI / 180;
        var ox = Math.cos(rad) * 14;
        var oy = Math.sin(rad) * 14;
        var anchor = 'middle';
        if (Math.cos(rad) > 0.3) {
            anchor = 'start';
        }
        if (Math.cos(rad) < -0.3) {
            anchor = 'end';
        }
        return '<text x="' + _r(x + ox) + '" y="' + _r(y + oy + 4)
            + '" fill="' + color + '" text-anchor="' + anchor + '">'
            + _esc(text) + '</text>';
    }

    /**
     * Round a number for SVG output.
     *
     * @param {number} n
     * @return {number}
     */
    function _r(n) {
        return Math.round(n * 10) / 10;
    }

    /**
     * Escape text for safe SVG insertion.
     *
     * @param {string} s
     * @return {string}
     */
    function _esc(s) {
        return String(s)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
    }

    return {render: render, rotate: rotate};
});
