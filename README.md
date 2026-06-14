# ChemIllusion Study Cards for Moodle (`local_chemillusion`)

A privacy-aware, open-source Moodle chemistry **study** plugin: molecule lookup,
RDKit.js-powered browser-side structure rendering, functional-group
highlighting, student flashcards, accessible summaries, and optional
ChemIllusion account linking for richer AI, image, video, and teacher
workflows.

> This plugin is **not** marketed as the first chemical drawing tool for Moodle.
> Moodle already has chemistry editors. Our wedge is RDKit-powered study tooling,
> accessibility, account linking, and ChemIllusion visual/AI escalation.

- **Component:** `local_chemillusion`
- **License:** GPL-3.0-or-later (see `LICENSE`)
- **Status:** Alpha (0.1.0) — Phase 1A + Phase 1B scaffolding
- **Bundled chemistry:** RDKit.js / RDKit WASM (BSD-3-Clause), lazy-loaded; see `THIRD_PARTY.md`

## Phases

| Phase | What it adds |
|------|--------------|
| **1A — Directory-safe baseline** | PHP-first plugin: admin settings, PubChem lookup with server cache, study decks/cards, reagent + functional-group dictionaries, account-link funnel, privacy provider. No RDKit dependency required. |
| **1B — RDKit WASM local mode** | Bundles RDKit.js/WASM in the plugin ZIP, lazy-loaded only on ChemIllusion tool pages: SMILES validation, SVG rendering, functional-group SMARTS matching, static atom/bond highlighting, richer cards. |

## What stays private

The full ChemIllusion Ketcher overlay, agent harness, MCP private inventory,
image/video generation internals, billing, and advanced ChemTutor remain in the
private `chem-art-generator` repository. This plugin only consumes **narrow,
public** ChemIllusion contracts and links.

## Install (admins)

1. Download a release ZIP (`dist/local_chemillusion-<version>-moodleXX.zip`).
2. In Moodle: **Site administration → Plugins → Install plugins** and upload the ZIP.
3. Complete the upgrade, then visit **Site administration → Plugins → Local plugins → ChemIllusion Study Cards** to configure mode, external services, and privacy.

No Composer, shell access, Conda, Python, or native packages are required.

## Privacy at a glance

- Default install is **local-only** with optional PubChem lookup and **no** account linking until an admin enables it.
- No grades, rosters, raw prompts, or student responses are sent to ChemIllusion.
- Admins can disable **every** external call with one switch.
- See `docs/privacy-and-data-flow.md` and the in-product `privacy.php` summary.

## Development

See `docs/local-dev-testing.md`. The local Moodle harness lives in the separate
`scottmreed/Moodle-plugin-dev` repository, which mounts this repo at
`/var/www/html/local/chemillusion`.

```bash
scripts/build-js.sh            # build AMD modules (dev)
scripts/update-rdkit-js.sh     # fetch the RDKit.js/WASM bundle (Phase 1B)
scripts/package-plugin.sh --version 0.1.0   # produce a release ZIP
scripts/run-local-ci.sh        # lint + unit checks via moodle-plugin-ci (if installed)
```

## Documentation

- `docs/license-boundary.md` — GPL boundary and what may/may not be copied from ChemIllusion.
- `docs/rdkit-wasm-packaging.md` — how RDKit.js is bundled and updated.
- `docs/privacy-and-data-flow.md` — exact data flows.
- `docs/release-checklist.md` — release steps.
- `docs/moodle-directory-submission.md` — Moodle Plugins Directory notes.

---

Maintained by MolLogic / Scott Reed.
