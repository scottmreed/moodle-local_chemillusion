# Root Diagram Creation Design

## Goal

Make molecule, Newman projection, reaction-coordinate, and orbital diagram creation directly available from the ChemIllusion root page. A user should reach a useful diagram with one click and should initially see only the controls needed to change it.

## Root Page

When graphical cards are enabled, the dashboard shows a "Create a chemistry diagram" gallery before the existing navigation. The gallery contains four links:

- Molecule structure, starting with `ClCCCCBr`
- Newman projection, starting with anti-butane
- Reaction coordinate, starting with a one-step exergonic profile
- Orbital diagram, starting with an ethene pi-bond example

Each tile has a compact chemistry-specific illustration, title, one-line description, and direct editor URL. The orbital illustration compares pi and sigma bonds and visually emphasizes pi. Existing dashboard links and saved-card browsing remain available below the gallery.

## Editor

Direct tool links pass a validated card type to the existing graphical-card editor. The editor locks that type and preloads a valid example. It does not ask the user to choose a card type again.

The primary controls are:

- Molecule: SMILES
- Newman: named preset, substituents, rotation, and energy hint
- Reaction coordinate: named reaction profile
- Orbital: named curated example or SMILES

Card name is prefilled from the selected example. Teacher notes, functional-group identifiers, raw points JSON, and atom index are advanced controls. They are visually secondary and collapsed where Moodle form APIs allow it.

The editor includes a preview region. Molecule, Newman, reaction-coordinate, and orbital changes update that preview using the existing local AMD renderers.

## Data And Validation

A `diagram_tool_catalog` class owns the four root tools, their enabled state, editor URLs, and logical defaults. The dashboard and editor consume this class so defaults are not duplicated.

Unknown or disabled tool parameters fall back to the generic editor rather than being trusted. Existing card storage schemas remain unchanged. Reaction templates are expanded into points when saving, and curated orbital examples store their template identifier so the matching shipped SVG is rendered.

## Testing

PHPUnit tests cover catalog contents, defaults, enabled-state filtering, and invalid tool lookup. Existing registry tests continue to validate the source templates. Browser verification covers the dashboard gallery, each direct editor path, previews, and responsive layout.

## Constraints

- No worktrees.
- No commits.
- No external service or network dependency.
- Reuse local templates, renderers, and shipped SVG assets.
