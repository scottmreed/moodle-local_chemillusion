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
 * SVG/PNG export helpers for graphical chemistry cards.
 *
 * All exports are client-side and do not require a server round-trip.
 * PNG export uses a hidden Canvas element to rasterise the SVG.
 *
 * @module     local_chemillusion/svg_exporter
 * @copyright  2026 MolLogic / Scott Reed
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define([], function() {

    'use strict';

    /**
     * Serialise an SVG element to a string.
     *
     * @param {SVGElement} svgEl
     * @return {string}
     */
    function serialise(svgEl) {
        var clone = svgEl.cloneNode(true);
        if (!clone.getAttribute('xmlns')) {
            clone.setAttribute('xmlns', 'http://www.w3.org/2000/svg');
        }
        return new XMLSerializer().serializeToString(clone);
    }

    /**
     * Trigger a file download.
     *
     * @param {string} dataUrl
     * @param {string} filename
     */
    function triggerDownload(dataUrl, filename) {
        var a = document.createElement('a');
        a.href = dataUrl;
        a.download = filename;
        a.style.display = 'none';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
    }

    /**
     * Download an SVG element as an .svg file.
     *
     * @param {SVGElement} svgEl
     * @param {string} filename
     */
    function exportSVG(svgEl, filename) {
        var svgStr = serialise(svgEl);
        var blob = new Blob([svgStr], {type: 'image/svg+xml;charset=utf-8'});
        var url = URL.createObjectURL(blob);
        triggerDownload(url, filename || 'chemillusion-card.svg');
        setTimeout(function() {
            URL.revokeObjectURL(url);
        }, 5000);
    }

    /**
     * Download an SVG element rasterised to PNG.
     *
     * @param {SVGElement} svgEl
     * @param {string} filename
     * @param {number} width Target pixel width (default 600).
     * @param {number} height Target pixel height (default 400).
     */
    function exportPNG(svgEl, filename, width, height) {
        width = width || 600;
        height = height || 400;

        var svgStr = serialise(svgEl);
        var svgBlob = new Blob([svgStr], {type: 'image/svg+xml;charset=utf-8'});
        var url = URL.createObjectURL(svgBlob);

        var img = new Image();
        img.onload = function() {
            var canvas = document.createElement('canvas');
            canvas.width = width;
            canvas.height = height;
            var ctx = canvas.getContext('2d');
            ctx.fillStyle = '#ffffff';
            ctx.fillRect(0, 0, width, height);
            ctx.drawImage(img, 0, 0, width, height);
            URL.revokeObjectURL(url);
            triggerDownload(canvas.toDataURL('image/png'), filename || 'chemillusion-card.png');
        };
        img.onerror = function() {
            URL.revokeObjectURL(url);
        };
        img.src = url;
    }

    /**
     * Copy SVG markup to the clipboard.
     *
     * @param {SVGElement} svgEl
     * @return {Promise}
     */
    function copySVGToClipboard(svgEl) {
        var svgStr = serialise(svgEl);
        if (navigator.clipboard && navigator.clipboard.writeText) {
            return navigator.clipboard.writeText(svgStr);
        }
        // Fallback for older browsers.
        var ta = document.createElement('textarea');
        ta.value = svgStr;
        ta.style.position = 'fixed';
        ta.style.opacity = '0';
        document.body.appendChild(ta);
        ta.select();
        document.execCommand('copy');
        document.body.removeChild(ta);
        return Promise.resolve();
    }

    /**
     * Copy a Moodle-pasteable HTML snippet (SVG + figcaption) to the clipboard.
     *
     * @param {SVGElement} svgEl
     * @param {string} summaryText Accessible text summary.
     * @return {Promise}
     */
    function copyMoodleSnippet(svgEl, summaryText) {
        var svgStr = serialise(svgEl);
        var escaped = (summaryText || '').replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
        var snippet = '<figure class="local-chemillusion-card-export">'
            + svgStr
            + '<figcaption>' + escaped + '</figcaption>'
            + '</figure>';

        if (navigator.clipboard && navigator.clipboard.writeText) {
            return navigator.clipboard.writeText(snippet);
        }
        var ta = document.createElement('textarea');
        ta.value = snippet;
        ta.style.position = 'fixed';
        ta.style.opacity = '0';
        document.body.appendChild(ta);
        ta.select();
        document.execCommand('copy');
        document.body.removeChild(ta);
        return Promise.resolve();
    }

    return {
        exportSVG: exportSVG,
        exportPNG: exportPNG,
        copySVGToClipboard: copySVGToClipboard,
        copyMoodleSnippet: copyMoodleSnippet,
    };
});
