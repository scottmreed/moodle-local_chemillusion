# Changelog

All notable changes to `local_chemillusion` are documented here. This project
adheres to [Semantic Versioning](https://semver.org/) and the Moodle plugin
versioning convention (`version.php`).

## [Unreleased]

### Added
- GitHub Actions CI (Moodle Plugin CI on 4.3/4.5) and release packaging workflow.
- Bug report issue template and pull request template.

### Changed
- Public repository preparation: removed internal dev/PRD documents; aligned repo name with Moodle convention (`moodle-local_chemillusion`).

## [0.2.0] - 2026-06-23

### Changed
- Renamed plugin display name from "ChemIllusion Study Cards" to **ChemIllusion Study Tools** throughout user-facing text and documentation.
- Aligned Moodle support documentation: range 4.3 through 5.0.x, automated CI on 4.3/4.5, local dev/testing on 5.0.8.

### Fixed
- Privacy API now fully covers `local_chemillusion_events` (metadata, context discovery, user listing, export, deletion).
- Privacy API now declares `pubchem_pug_rest` as an external location.
- `lookup_molecule` AJAX endpoint: `alt_types` return schema corrected from scalar to array.
- All top-level page scripts now use plugin-relative `require_once(__DIR__ . '/../../config.php')` instead of fragile `$_SERVER['DOCUMENT_ROOT']` bootstrap.
- Release packaging (`scripts/package-plugin.sh`) now fails hard when RDKit assets are missing; added `--without-rdkit` flag for local-only builds.
- RDKit.js pin updated to npm version `2024.3.5-1.0.0` (previous `2024.09.6` URL returned 404 on unpkg).

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

[Unreleased]: https://github.com/scottmreed/moodle-local_chemillusion/compare/v0.2.0...HEAD
[0.2.0]: https://github.com/scottmreed/moodle-local_chemillusion/compare/v0.1.0...v0.2.0
[0.1.0]: https://github.com/scottmreed/moodle-local_chemillusion/releases/tag/v0.1.0
