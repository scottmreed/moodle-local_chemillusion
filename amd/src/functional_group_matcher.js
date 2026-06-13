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
 * Functional-group detection via RDKit.js SMARTS substructure matching.
 *
 * @module     local_chemillusion/functional_group_matcher
 * @copyright  2026 MolLogic / Scott Reed
 * @license    GPL-3.0-or-later
 */
define(['local_chemillusion/rdkit_loader'], function(Loader) {

    'use strict';

    /**
     * Decide whether a substruct-match JSON result indicates a hit.
     * @param {String} result
     * @return {Boolean}
     */
    var hasMatch = function(result) {
        if (!result || result === '[]' || result === '{}' || result === '') {
            return false;
        }
        try {
            var parsed = JSON.parse(result);
            if (Array.isArray(parsed)) {
                return parsed.length > 0;
            }
            if (parsed && parsed.atoms) {
                return parsed.atoms.length > 0;
            }
        } catch (e) {
            return true;
        }
        return true;
    };

    return {
        /**
         * Detect which registered functional groups appear in a molecule.
         * @param {String} smiles
         * @param {Array} registry Array of {id, label, smarts}.
         * @return {Promise<Array>}
         */
        detect: function(smiles, registry) {
            registry = registry || [];
            return Loader.load().then(function(rdkit) {
                var mol = rdkit.get_mol(smiles || '');
                var found = [];
                if (mol && mol.is_valid()) {
                    registry.forEach(function(group) {
                        var qmol = null;
                        try {
                            qmol = rdkit.get_qmol(group.smarts);
                            if (qmol) {
                                var matches = mol.get_substruct_matches(qmol);
                                if (hasMatch(matches)) {
                                    found.push({
                                        id: group.id,
                                        label: group.label,
                                        smarts: group.smarts
                                    });
                                }
                            }
                        } catch (e) {
                            // Skip a single bad SMARTS; keep scanning.
                        } finally {
                            if (qmol) {
                                qmol.delete();
                            }
                        }
                    });
                }
                if (mol) {
                    mol.delete();
                }
                return found;
            });
        }
    };
});
