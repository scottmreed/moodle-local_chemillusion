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
 * Reaction coordinate diagram SVG renderer.
 *
 * Accepts a list of normalised energy points and renders a smooth Bézier
 * curve with labelled transition states, intermediates, annotations, and axes.
 *
 * Points use normalised coordinates: x in [0,1], y in [0,1] where
 * y=0 is the TOP of the chart (high energy) and y=1 is the bottom (low energy).
 *
 * @module     local_chemillusion/reaction_coordinate_diagram
 * @copyright  2026 MolLogic / Scott Reed
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define([], function() {

    'use strict';

    var W        = 520;
    var H        = 300;
    var PAD_L    = 60;  // left padding (for y-axis label)
    var PAD_R    = 20;
    var PAD_T    = 20;
    var PAD_B    = 40;  // bottom padding (for x-axis label)
    var PLOT_W   = W - PAD_L - PAD_R;
    var PLOT_H   = H - PAD_T - PAD_B;

    /**
     * Render an SVG reaction coordinate diagram from card data.
     *
     * @param {Object} data  { title, points, annotations, x_axis, y_axis, disclaimer }
     * @return {string}  SVG markup string.
     */
    function render(data) {
        var points      = data.points      || [];
        var annotations = data.annotations || [];
        var xLabel      = data.x_axis || 'Reaction coordinate';
        var yLabel      = data.y_axis || 'Free energy';
        var disclaimer  = data.disclaimer !== false;

        if (points.length < 2) {
            return _errorSVG('Need at least 2 points.');
        }

        var px = _mapPoints(points);

        var parts = [];
        parts.push('<svg xmlns="http://www.w3.org/2000/svg"'
            + ' width="' + W + '" height="' + (disclaimer ? H + 18 : H) + '"'
            + ' viewBox="0 0 ' + W + ' ' + (disclaimer ? H + 18 : H) + '"'
            + ' role="img" aria-label="' + _esc(data.title || 'Reaction coordinate diagram') + '"'
            + ' font-family="sans-serif" font-size="11">');

        // Background.
        parts.push('<rect width="' + W + '" height="' + H
            + '" fill="#fafafa" stroke="#dee2e6" stroke-width="1"/>');

        // Axes.
        parts.push(_axis(xLabel, yLabel));

        // Smooth curve through points.
        parts.push('<path d="' + _cardinalSpline(px) + '"'
            + ' fill="none" stroke="#0d6efd" stroke-width="2.5" stroke-linejoin="round"/>');

        // Point markers and labels.
        px.forEach(function(p) {
            var isTS  = (p.id || '').toLowerCase().indexOf('ts') === 0;
            var isInt = (p.id || '').toLowerCase().indexOf('int') === 0;
            var fill  = isTS ? '#dc3545' : isInt ? '#fd7e14' : '#0d6efd';
            parts.push('<circle cx="' + _r(p.sx) + '" cy="' + _r(p.sy)
                + '" r="4" fill="' + fill + '" stroke="white" stroke-width="1.5"/>');
            parts.push(_ptLabel(p));
        });

        // Annotations.
        annotations.forEach(function(ann) {
            var fromPt = px.find(function(p) { return p.id === ann.from; });
            var toPt   = px.find(function(p) { return p.id === ann.to; });
            if (!fromPt || !toPt) { return; }
            if (ann.type === 'arrow') {
                parts.push(_eaArrow(fromPt, toPt, ann.label || ''));
            } else if (ann.type === 'bracket') {
                parts.push(_deltaGBracket(fromPt, toPt, ann.label || ''));
            }
        });

        // Disclaimer.
        if (disclaimer) {
            parts.push('<text x="' + _r(PAD_L) + '" y="' + (H + 14)
                + '" fill="#6c757d" font-size="9" font-style="italic">'
                + 'Qualitative teaching diagram — values not experimental.</text>');
        }

        parts.push('</svg>');
        return parts.join('\n');
    }

    function _mapPoints(points) {
        return points.map(function(p) {
            return Object.assign({}, p, {
                sx: PAD_L + p.x * PLOT_W,
                sy: PAD_T + p.y * PLOT_H,
            });
        });
    }

    function _axis(xLabel, yLabel) {
        var x0 = PAD_L, y0 = PAD_T;
        var x1 = PAD_L + PLOT_W, y1 = PAD_T + PLOT_H;
        return [
            '<line x1="' + x0 + '" y1="' + y0 + '" x2="' + x0 + '" y2="' + y1
                + '" stroke="#495057" stroke-width="1.5"/>',
            '<line x1="' + x0 + '" y1="' + y1 + '" x2="' + x1 + '" y2="' + y1
                + '" stroke="#495057" stroke-width="1.5"/>',
            '<text x="' + _r((x0 + x1) / 2) + '" y="' + (y1 + 28)
                + '" text-anchor="middle" fill="#333">' + _esc(xLabel) + '</text>',
            '<text x="' + (x0 - 10) + '" y="' + _r((y0 + y1) / 2)
                + '" text-anchor="middle" fill="#333"'
                + ' transform="rotate(-90,' + (x0 - 10) + ',' + _r((y0 + y1) / 2) + ')">'
                + _esc(yLabel) + '</text>',
        ].join('\n');
    }

    function _cardinalSpline(pts) {
        if (pts.length === 2) {
            return 'M ' + _r(pts[0].sx) + ' ' + _r(pts[0].sy)
                + ' L ' + _r(pts[1].sx) + ' ' + _r(pts[1].sy);
        }
        var t = 0.4; // tension
        var d = 'M ' + _r(pts[0].sx) + ' ' + _r(pts[0].sy);
        for (var i = 0; i < pts.length - 1; i++) {
            var p0 = pts[i - 1] || pts[i];
            var p1 = pts[i];
            var p2 = pts[i + 1];
            var p3 = pts[i + 2] || pts[i + 1];
            var cp1x = p1.sx + t * (p2.sx - p0.sx) / 3;
            var cp1y = p1.sy + t * (p2.sy - p0.sy) / 3;
            var cp2x = p2.sx - t * (p3.sx - p1.sx) / 3;
            var cp2y = p2.sy - t * (p3.sy - p1.sy) / 3;
            d += ' C ' + _r(cp1x) + ' ' + _r(cp1y)
               + ', ' + _r(cp2x) + ' ' + _r(cp2y)
               + ', ' + _r(p2.sx) + ' ' + _r(p2.sy);
        }
        return d;
    }

    function _ptLabel(p) {
        var isTS  = (p.id || '').toLowerCase().indexOf('ts') === 0;
        var dy    = isTS ? -12 : 18;
        return '<text x="' + _r(p.sx) + '" y="' + _r(p.sy + dy)
            + '" text-anchor="middle" fill="#333" font-size="10">'
            + _esc(p.label || '') + '</text>';
    }

    function _eaArrow(from, to, label) {
        var midX = _r((from.sx + to.sx) / 2);
        var x    = _r(from.sx);
        var y1   = _r(from.sy);
        var y2   = _r(to.sy);
        return '<line x1="' + x + '" y1="' + y1 + '" x2="' + x + '" y2="' + y2
            + '" stroke="#dc3545" stroke-width="1" stroke-dasharray="3,2"'
            + ' marker-end="url(#arrowhead)"/>'
            + '<text x="' + _r(from.sx - 8) + '" y="' + _r((from.sy + to.sy) / 2)
            + '" text-anchor="end" fill="#dc3545" font-size="9">' + _esc(label) + '</text>';
    }

    function _deltaGBracket(from, to, label) {
        var x    = _r(Math.max(from.sx, to.sx) + 14);
        var y1   = _r(from.sy);
        var y2   = _r(to.sy);
        return '<line x1="' + x + '" y1="' + y1 + '" x2="' + x + '" y2="' + y2
            + '" stroke="#198754" stroke-width="1.5"/>'
            + '<text x="' + _r(parseFloat(x) + 4) + '" y="' + _r((from.sy + to.sy) / 2)
            + '" fill="#198754" font-size="9">' + _esc(label) + '</text>';
    }

    function _errorSVG(msg) {
        return '<svg xmlns="http://www.w3.org/2000/svg" width="' + W + '" height="60">'
            + '<text x="10" y="30" fill="#dc3545" font-family="sans-serif" font-size="12">'
            + _esc(msg) + '</text></svg>';
    }

    function _r(n) { return Math.round(n * 10) / 10; }

    function _esc(s) {
        return String(s)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    return { render: render };
});
