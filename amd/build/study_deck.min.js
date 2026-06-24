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
 * Teacher deck creation: paste molecules, resolve via PubChem, save a deck.
 *
 * @module     local_chemillusion/study_deck
 * @copyright  2026 MolLogic / Scott Reed
 * @license    GPL-3.0-or-later
 */
define(['core/ajax', 'core/notification'], function(Ajax, Notification) {

    'use strict';

    var courseid = 0;

    /**
     * Resolve a single identifier.
     * @param {String} query
     * @return {Promise}
     */
    var resolve = function(query) {
        return Ajax.call([{
            methodname: 'local_chemillusion_lookup_molecule',
            args: {query: query}
        }])[0];
    };

    /**
     * Persist a deck and its cards.
     * @param {String} name
     * @param {Array} cards
     * @return {Promise}
     */
    var save = function(name, cards) {
        return Ajax.call([{
            methodname: 'local_chemillusion_save_deck',
            args: {name: name, courseid: courseid, visibility: 'course', cards: cards}
        }])[0];
    };

    /**
     * Build the inline create form.
     * @param {Element} host
     */
    var showForm = function(host) {
        if (host.querySelector('[data-region="deck-form"]')) {
            return;
        }
        var box = document.createElement('div');
        box.setAttribute('data-region', 'deck-form');
        box.className = 'mt-2';
        box.innerHTML =
            '<div class="form-group">' +
                '<label for="local-chemillusion-deckname">' + 'Deck name' + '</label>' +
                '<input type="text" class="form-control" id="local-chemillusion-deckname">' +
            '</div>' +
            '<div class="form-group">' +
                '<label for="local-chemillusion-decklist">One molecule name or SMILES per line</label>' +
                '<textarea class="form-control" id="local-chemillusion-decklist" rows="6"></textarea>' +
            '</div>' +
            '<button type="button" class="btn btn-primary" data-action="save-deck">Save deck</button> ' +
            '<span data-region="deck-status" aria-live="polite"></span>';
        host.appendChild(box);

        box.querySelector('[data-action="save-deck"]').addEventListener('click', function() {
            buildAndSave(box);
        });
    };

    /**
     * Resolve all lines, build molecule cards, and save.
     * @param {Element} box
     */
    var buildAndSave = function(box) {
        var name = box.querySelector('#local-chemillusion-deckname').value.trim();
        var lines = box.querySelector('#local-chemillusion-decklist').value
            .split('\n').map(function(l) {
                return l.trim();
            }).filter(Boolean);
        var status = box.querySelector('[data-region="deck-status"]');
        if (!name || !lines.length) {
            status.textContent = 'Enter a name and at least one molecule.';
            return;
        }
        status.textContent = 'Resolving ' + lines.length + ' molecules…';

        Promise.all(lines.map(function(line) {
            return resolve(line).catch(function() {
                return {status: 'error'};
            });
        })).then(function(results) {
            var cards = [];
            results.forEach(function(r) {
                if (r.status === 'ok') {
                    /* eslint-disable camelcase */
                    cards.push({
                        cardtype: 'molecule_identity',
                        prompt: r.name || '',
                        answer: ['Formula: ' + (r.formula || ''),
                            'MW: ' + (r.mw || ''),
                            'CID: ' + (r.cid || ''),
                            'SMILES: ' + (r.canonical_smiles || '')].join('\n'),
                        molecule_payload: JSON.stringify({
                            cid: r.cid, formula: r.formula,
                            canonical_smiles: r.canonical_smiles, inchikey: r.inchikey
                        })
                    });
                    /* eslint-enable camelcase */
                }
            });
            if (!cards.length) {
                status.textContent = 'No molecules could be resolved.';
                return null;
            }
            return save(name, cards);
        }).then(function(res) {
            if (!res) {
                return null;
            }
            status.textContent = 'Saved ' + res.cardcount + ' cards.';
            window.location.reload();
            return null;
        }).catch(Notification.exception);
    };

    return {
        /**
         * Initialise deck creation.
         */
        init: function() {
            var host = document.querySelector('[data-region="deck-create"]');
            if (!host) {
                return;
            }
            courseid = parseInt(host.getAttribute('data-courseid'), 10) || 0;
            var btn = host.querySelector('[data-action="create-deck"]');
            if (btn) {
                btn.addEventListener('click', function() {
                    showForm(host);
                });
            }
        }
    };
});
