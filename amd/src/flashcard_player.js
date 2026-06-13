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
 * Accessible, keyboard-operable flashcard player. Cards never auto-flip.
 *
 * @module     local_chemillusion/flashcard_player
 * @copyright  2026 MolLogic / Scott Reed
 * @license    GPL-3.0-or-later
 */
define(['core/str'], function(Str) {

    'use strict';

    var cards = [];
    var idx = 0;
    var revealed = false;
    var root = null;
    var strings = {};

    /**
     * Escape text for safe HTML insertion.
     * @param {String} text
     * @return {String}
     */
    var esc = function(text) {
        var div = document.createElement('div');
        div.textContent = text === null || text === undefined ? '' : String(text);
        return div.innerHTML;
    };

    /**
     * Render the current card.
     */
    var render = function() {
        if (!root) {
            return;
        }
        if (!cards.length) {
            root.innerHTML = '<p class="text-muted">' + esc(strings.empty || '') + '</p>';
            return;
        }
        var c = cards[idx];
        var progress = (strings.progress || '{$a->index}/{$a->total}')
            .replace('{$a->index}', String(idx + 1))
            .replace('{$a->total}', String(cards.length));
        var back = revealed
            ? '<div class="local-chemillusion-back" data-region="back">' + esc(c.answer).replace(/\n/g, '<br>') + '</div>'
            : '';
        root.innerHTML =
            '<div class="card local-chemillusion-flashcard">' +
                '<div class="card-body">' +
                    '<p class="text-muted small">' + esc(progress) + '</p>' +
                    '<div class="local-chemillusion-front" data-region="front">' +
                        esc(c.prompt).replace(/\n/g, '<br>') + '</div>' +
                    back +
                '</div>' +
                '<div class="card-footer">' +
                    '<button type="button" class="btn btn-primary" data-action="show-answer" aria-expanded="' +
                        (revealed ? 'true' : 'false') + '">' + esc(strings.show || 'Show answer') + '</button> ' +
                    '<button type="button" class="btn btn-outline-secondary" data-action="prev">' +
                        esc(strings.prev || 'Previous') + '</button> ' +
                    '<button type="button" class="btn btn-outline-secondary" data-action="next">' +
                        esc(strings.next || 'Next') + '</button>' +
                '</div>' +
            '</div>';
    };

    /**
     * Move to a card index.
     * @param {Number} next
     */
    var go = function(next) {
        if (!cards.length) {
            return;
        }
        idx = (next + cards.length) % cards.length;
        revealed = false;
        render();
    };

    /**
     * Click delegation.
     * @param {Event} e
     */
    var onClick = function(e) {
        var action = e.target.getAttribute('data-action');
        if (action === 'show-answer') {
            revealed = !revealed;
            render();
        } else if (action === 'next') {
            go(idx + 1);
        } else if (action === 'prev') {
            go(idx - 1);
        }
    };

    /**
     * Keyboard handling.
     * @param {Event} e
     */
    var onKey = function(e) {
        if (e.key === 'ArrowRight') {
            go(idx + 1);
        } else if (e.key === 'ArrowLeft') {
            go(idx - 1);
        } else if (e.key === 'Enter' || e.key === ' ') {
            revealed = !revealed;
            render();
            e.preventDefault();
        }
    };

    return {
        /**
         * Initialise the player.
         * @param {Object} cfg
         */
        init: function(cfg) {
            cards = (cfg && cfg.cards) || [];
            root = document.getElementById('local-chemillusion-flashcards');
            if (!root) {
                return;
            }
            Str.get_strings([
                {key: 'flashcard_show_answer', component: 'local_chemillusion'},
                {key: 'flashcard_next', component: 'local_chemillusion'},
                {key: 'flashcard_prev', component: 'local_chemillusion'},
                {key: 'deck_empty', component: 'local_chemillusion'}
            ]).then(function(s) {
                strings = {show: s[0], next: s[1], prev: s[2], empty: s[3],
                    progress: '{$a->index}/{$a->total}'};
                render();
                return null;
            }).catch(function() {
                render();
                return null;
            });
            root.addEventListener('click', onClick);
            root.addEventListener('keydown', onKey);
        }
    };
});
