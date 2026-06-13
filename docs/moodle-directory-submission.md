# Moodle Plugins Directory submission notes

These notes prepare `local_chemillusion` for the Moodle Plugins Directory.

## Positioning

Submit as a **chemistry study** plugin, not "the first chemical drawing tool".
Lead with: RDKit-powered browser study cards, accessibility, functional-group
recognition, account linking, and optional ChemIllusion visual/AI escalation.

## Requirements checklist

- [x] GPL-3.0-or-later license (`LICENSE`).
- [x] `README.md`, `CHANGELOG.md`, `CONTRIBUTING.md`, `SECURITY.md`.
- [x] `version.php` with component, version, requires, supported, maturity, release.
- [x] Language strings in `lang/en/local_chemillusion.php`.
- [x] Privacy API provider (`classes/privacy/provider.php`).
- [x] Capabilities defined in `db/access.php` with matching strings.
- [x] Third-party libs declared in `thirdpartylibs.xml` (RDKit.js, BSD-3-Clause).
- [x] No Composer/shell required to install from ZIP.
- [x] Tests: PHPUnit + Behat scaffolding; Moodle Plugin CI workflow.

## Reviewer-sensitive points (and our mitigations)

- **Bundled WASM**: Phase 1B is optional, documented, license-clean, lazy-loaded,
  and not required for the baseline plugin. See `docs/rdkit-wasm-packaging.md`.
- **ZIP size**: minimal RDKit build + lazy loading; size documented.
- **External calls**: all optional and admin-controllable; a master kill-switch
  disables every outbound request. See `docs/privacy-and-data-flow.md`.
- **GPL boundary**: no private ChemIllusion internals are included. See
  `docs/license-boundary.md`.

## Supported Moodle versions

Targets Moodle 4.3+ (`$plugin->supported = [403, 405]`). CI runs against
`MOODLE_403_STABLE` and `MOODLE_405_STABLE`.
