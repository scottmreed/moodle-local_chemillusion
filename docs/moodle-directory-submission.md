# Moodle Plugins Directory submission notes

These notes prepare `local_chemillusion` for the Moodle Plugins Directory.
They are based on Moodle's plugin contribution, checklist, code precheck,
third-party library, and plugin documentation guidance.

## Positioning

Submit as a **chemistry study** plugin, not "the first chemical drawing tool".
Lead with: RDKit-powered browser-side structure rendering, accessibility,
functional-group recognition, account linking, and optional ChemIllusion
visual/AI escalation.

## No subscription required

**ChemIllusion Study Tools works locally without a ChemIllusion subscription.**
Optional account linking / SaaS escalation requires a ChemIllusion account and
may require a paid ChemIllusion plan. Review/demo credentials are available on
request.

## Requirements checklist

- [x] `local_chemillusion` frankenstyle component remains unchanged
- [x] User-facing name is **ChemIllusion Study Tools**
- [x] GPL-3.0-or-later license (`LICENSE`)
- [x] `README.md`, `CHANGELOG.md`, `CONTRIBUTING.md`, `SECURITY.md`
- [x] Repository root is the plugin root (`version.php`, `classes/`, `db/`,
  `lang/`, and `templates/` are not nested under an extra directory)
- [x] Repository name follows Moodle convention: `moodle-local_chemillusion`
- [x] `version.php` with component, version, requires, supported, maturity, release
- [x] Language strings in `lang/en/local_chemillusion.php`
- [x] Privacy API covers links, decks, cards, events, ChemIllusion external
  location, and PubChem external location
- [x] External calls are optional and admin-controllable
- [x] Master external-call kill switch exists (`disable_external`)
- [x] Local use works without ChemIllusion subscription
- [x] Optional account linking / SaaS escalation disclosure is present
- [x] RDKit third-party library is declared in `thirdpartylibs.xml`
- [x] Packaging fails hard if RDKit is expected but missing
- [x] Release ZIP installs from Moodle plugin installer without Composer,
  shell, Python, Conda, or native packages
- [x] Capabilities defined in `db/access.php` with matching strings
- [x] Tests: PHPUnit scaffolding; Moodle Plugin CI workflow
- [x] Supported range: 4.3 through 5.0.x
- [x] Automated CI: 4.3 and 4.5
- [x] Local dev/testing: 5.0.8
- [x] Public issue tracker enabled on GitHub before submission
- [x] Public repository visibility before submission
- [x] Screenshots captured for Moodle Plugins Directory listing
- [ ] Release ZIP smoke-tested with developer debugging enabled

## Reviewer-sensitive points (and our mitigations)

- **Bundled WASM**: Phase 1B is optional, documented, license-clean,
  lazy-loaded, and not required for the baseline plugin. See
  `docs/rdkit-wasm-packaging.md`.
- **ZIP size**: minimal RDKit build + lazy loading; size documented.
- **External calls**: all optional and admin-controllable; a master kill-switch
  (`disable_external`) disables every outbound request. See
  `docs/privacy-and-data-flow.md`.
- **Parallel ChemIllusion server**: the plugin is not hard-wired to a private
  server. The ChemIllusion base URL is an admin setting, account linking is
  off by default, and local study tools work without a ChemIllusion
  subscription.
- **GPL boundary**: no private ChemIllusion internals are included. See
  `docs/license-boundary.md`.
- **Privacy**: the plugin implements Moodle's Privacy API covering links,
  decks, cards, events, and both external locations (ChemIllusion SaaS and
  PubChem). Any new external integration must update both the provider and
  this document.
- **Namespace/collision safety**: database tables, capabilities, classes,
  strings, AMD modules, templates, and settings use the `local_chemillusion`
  frankenstyle prefix.

## Supported Moodle versions

Supported range: Moodle 4.3 through Moodle 5.0.x.
Automated CI: Moodle 4.3 and Moodle 4.5.
Local development/testing: Moodle 5.0.8.

In `version.php`:
```php
$plugin->requires  = 2023100900; // Moodle 4.3.0 and later.
$plugin->supported = [403, 500]; // Moodle 4.3 through 5.0.x.
```

CI runs against `MOODLE_403_STABLE` and `MOODLE_405_STABLE`. Do not claim
automated CI on Moodle 5.0 unless a 5.0 CI job is added and passing.
