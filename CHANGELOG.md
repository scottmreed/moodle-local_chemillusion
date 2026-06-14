# Changelog

All notable changes to `local_chemillusion` are documented here. This project
adheres to [Semantic Versioning](https://semver.org/) and the Moodle plugin
versioning convention (`version.php`).

## [Unreleased]

## [0.1.0] - 2026-06-13

Initial Phase 1A + Phase 1B scaffold.

### Added
- Admin settings: operating mode, external-service toggles, ChemIllusion
  connection (base URL + signing secret), and privacy controls.
- PubChem PUG REST client with server-side cache and a cleanup scheduled task.
- Molecule lookup page with progressive-enhancement (works without JavaScript)
  and a live AJAX lookup web service (`local_chemillusion_lookup_molecule`).
- Study decks and cards: data model, repository, card generator, and a
  `local_chemillusion_save_deck` web service.
- Reagent (Organic 1/2) and functional-group dictionaries (with SMARTS).
- Account-link funnel: signed, time-limited launch state and minimal local
  mapping table; no grades or rosters are sent.
- Local-only usage counters and a teacher/admin dashboard.
- Privacy API provider covering links, decks, cards, and the external link.
- Phase 1B RDKit.js/WASM lazy loader, SVG renderer, SMILES validation,
  functional-group matcher, and static highlighting (binary fetched via
  `scripts/update-rdkit-js.sh`).
- Accessible, keyboard-operable flashcard player (no auto-flip).
- PHPUnit and Behat test scaffolding, Moodle Plugin CI, and packaging scripts.

[Unreleased]: https://github.com/scottmreed/moodle-local-chemillusion/compare/v0.1.0...HEAD
[0.1.0]: https://github.com/scottmreed/moodle-local-chemillusion/releases/tag/v0.1.0
