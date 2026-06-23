# pix/orbitals/

Curated SVG orbital overlay assets for local_chemillusion graphical cards.

Each file corresponds to an entry in `data/orbital_templates.json` with
`"confidence": "curated"`. The filename must match the template `id`.

## Initial assets (hand-authored)

- `ethene_pi_bond.svg` — ethene showing π bond p-orbital overlap
- `benzene_pi_cloud.svg` — benzene aromatic π electron cloud
- `butadiene_conjugation.svg` — 1,3-butadiene conjugated π system
- `acetylene_sp.svg` — acetylene sp triple-bond
- `formaldehyde_carbonyl.svg` — formaldehyde C=O π bond
- `allyl_cation.svg` — allylic cation resonance
- `pyridine_pi_system.svg` — pyridine heteroaromatic π system
- `furan_pi_system.svg` — furan heteroaromatic π system
- `allene_orthogonal_pi.svg` — allene orthogonal π systems

## Adding assets

1. Design the SVG (300×240 recommended; `viewBox="0 0 300 240"`).
2. Include an accessible `<title>` element.
3. Use WCAG-compliant colours (check contrast for lobe fills).
4. Name the file `<template_id>.svg` matching `data/orbital_templates.json`.
5. Test in both light and high-contrast OS modes.

## Placeholder note

The actual SVG files are authored separately. Until they exist, the
orbital card will show the graceful fallback CTA ("Open in ChemIllusion").
