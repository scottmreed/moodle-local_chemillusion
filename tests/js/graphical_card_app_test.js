'use strict';

const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');

let app;
const sandbox = {
    define: (dependencies, factory) => {
        app = factory({}, {}, {});
    },
    document: {},
    window: {},
    M: {util: {get_string: () => ''}},
    require: () => {},
    fetch: () => Promise.reject(new Error('not used')),
    console,
};

vm.runInNewContext(
    fs.readFileSync('amd/src/graphical_card_app.js', 'utf8'),
    sandbox,
    {filename: 'graphical_card_app.js'}
);

const newman = app.buildPreviewData('newman', {
    newman_front_a: 'CH3',
    newman_front_b: 'H',
    newman_front_c: 'H',
    newman_back_a: 'CH3',
    newman_back_b: 'H',
    newman_back_c: 'H',
    newman_rotation: '180',
}, {});
assert.deepEqual(Array.from(newman.front), ['CH3', 'H', 'H']);
assert.equal(newman.rotation_degrees, 180);

const reaction = app.buildPreviewData('reaction', {
    rcd_template: 'one_step_exergonic',
    rcd_points_json: '[{"id":"r","x":0,"y":0.5},{"id":"p","x":1,"y":0.7}]',
}, {
    one_step_exergonic: {title: 'Exergonic', annotations: [{type: 'arrow'}]},
});
assert.equal(reaction.title, 'Exergonic');
assert.equal(reaction.points.length, 2);

const orbital = app.buildPreviewData('orbital', {
    orbital_template: 'ethene_pi_bond',
    orbital_smiles: 'C=C',
    orbital_atom_idx: '0',
}, {
    ethene_pi_bond: {template_id: 'ethene_pi_bond', description: 'Pi bonding'},
});
assert.equal(orbital.template_id, 'ethene_pi_bond');
assert.equal(orbital.smiles, 'C=C');

const molecule = app.buildPreviewData('molecule', {smiles: 'ClCCCCBr'}, {});
assert.equal(molecule.smiles, 'ClCCCCBr');

console.log('graphical_card_app preview data tests passed');
