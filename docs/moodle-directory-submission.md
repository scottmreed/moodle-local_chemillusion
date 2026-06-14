# Moodle Plugins Directory submission notes

These notes prepare `local_chemillusion` for the Moodle Plugins Directory.
They are based on Moodle's plugin contribution, checklist, code precheck,
third-party library, and plugin documentation guidance.

## Positioning

Submit as a **chemistry study** plugin, not "the first chemical drawing tool".
Lead with: RDKit-powered browser study cards, accessibility, functional-group
recognition, account linking, and optional ChemIllusion visual/AI escalation.

## Requirements checklist

- [x] GPL-3.0-or-later license (`LICENSE`).
- [x] `README.md`, `CHANGELOG.md`, `CONTRIBUTING.md`, `SECURITY.md`.
- [x] Repository root is the plugin root (`version.php`, `classes/`, `db/`,
  `lang/`, and `templates/` are not nested under an extra directory).
- [x] Repository name follows Moodle convention: `moodle-local_chemillusion`.
- [x] `version.php` with component, version, requires, supported, maturity, release.
- [x] Language strings in `lang/en/local_chemillusion.php`.
- [x] Privacy API provider (`classes/privacy/provider.php`).
- [x] Capabilities defined in `db/access.php` with matching strings.
- [x] Third-party libs declared in `thirdpartylibs.xml` (RDKit.js, BSD-3-Clause).
- [x] No Composer/shell required to install from ZIP.
- [x] Tests: PHPUnit + Behat scaffolding; Moodle Plugin CI workflow.
- [ ] Public issue tracker enabled on GitHub before submission.
- [ ] Public documentation URL created, ideally in Moodle Docs after directory
  record creation.
- [ ] Screenshots captured for the plugin directory listing.
- [ ] Release ZIP produced from a clean checkout and installed through Moodle's
  plugin installer.
- [ ] Moodle Plugin CI run with phplint, codechecker, phpdoc, validate,
  savepoints, mustache, grunt, phpunit, and Behat checks.

## Reviewer-sensitive points (and our mitigations)

- **Bundled WASM**: Phase 1B is optional, documented, license-clean, lazy-loaded,
  and not required for the baseline plugin. See `docs/rdkit-wasm-packaging.md`.
- **ZIP size**: minimal RDKit build + lazy loading; size documented.
- **External calls**: all optional and admin-controllable; a master kill-switch
  disables every outbound request. See `docs/privacy-and-data-flow.md`.
- **Parallel ChemIllusion server**: the plugin is not hard-wired to a private
  server. The ChemIllusion base URL is an admin setting, account linking is off
  by default, and local study tools work without ChemIllusion.
- **GPL boundary**: no private ChemIllusion internals are included. See
  `docs/license-boundary.md`.
- **Privacy**: the plugin implements Moodle's Privacy API and documents
  external metadata fields. Any new external integration must update both.
- **Namespace/collision safety**: database tables, capabilities, classes,
  strings, AMD modules, templates, and settings use the `local_chemillusion`
  frankenstyle prefix.

## Supported Moodle versions

Targets Moodle 4.3+ (`$plugin->supported = [403, 405]`). CI runs against
`MOODLE_403_STABLE` and `MOODLE_405_STABLE`.
