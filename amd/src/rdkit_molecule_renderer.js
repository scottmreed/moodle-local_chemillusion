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
 * RDKit.js molecule rendering, validation, and static highlight helpers.
 *
 * @module     local_chemillusion/rdkit_molecule_renderer
 * @copyright  2026 MolLogic / Scott Reed
 * @license    GPL-3.0-or-later
 */
define(['local_chemillusion/rdkit_loader'], function(Loader) {

    'use strict';

    return {
        /**
         * Validate a SMILES string in the browser.
         * @param {String} smiles
         * @return {Promise<Object>} {valid: Boolean}
         */
        validate: function(smiles) {
            return Loader.load().then(function(rdkit) {
                var mol = rdkit.get_mol(smiles || '');
                var valid = Boolean(mol && mol.is_valid());
                if (mol) {
                    mol.delete();
                }
                return {valid: valid};
            });
        },

        /**
         * Render an SVG for a SMILES string into a target element.
         * @param {Element} el
         * @param {String} smiles
         * @return {Promise<Boolean>}
         */
        render: function(el, smiles) {
            return Loader.load().then(function(rdkit) {
                var mol = rdkit.get_mol(smiles || '');
                if (!mol || !mol.is_valid()) {
                    if (mol) {
                        mol.delete();
                    }
                    return false;
                }
                el.innerHTML = mol.get_svg(320, 240);
                mol.delete();
                return true;
            }).catch(function() {
                el.setAttribute('data-rdkit-error', '1');
                return false;
            });
        },

        /**
         * Render an SVG with a substructure highlight.
         * @param {Element} el
         * @param {String} smiles
         * @param {String} smarts
         * @return {Promise<Boolean>}
         */
        highlight: function(el, smiles, smarts) {
            return Loader.load().then(function(rdkit) {
                var mol = rdkit.get_mol(smiles || '');
                var qmol = rdkit.get_qmol(smarts || '');
                if (!mol || !mol.is_valid() || !qmol) {
                    if (mol) {
                        mol.delete();
                    }
                    if (qmol) {
                        qmol.delete();
                    }
                    return false;
                }
                var match = mol.get_substruct_match(qmol);
                var details = (match && match !== '{}') ? match : '{}';
                el.innerHTML = mol.get_svg_with_highlights(details);
                mol.delete();
                qmol.delete();
                return true;
            }).catch(function() {
                return false;
            });
        }
    };
});
