# Local development & testing

This plugin is developed against a local Moodle. The recommended harness is the
separate **`scottmreed/Moodle-plugin-dev`** repository, which runs Moodle in
Docker and mounts this repo at `/var/www/html/local/chemillusion`.

## Option A — Moodle-plugin-dev harness (recommended)

1. Clone both repos as siblings:
   ```text
   parent/
     moodle-local-chemillusion/   <- this repo
     Moodle-plugin-dev/           <- harness
   ```
2. In `Moodle-plugin-dev`, start the stack (see that repo's README). It mounts
   `../moodle-local-chemillusion -> /var/www/html/local/chemillusion`.
3. Run the Moodle upgrade to install `local_chemillusion`.
4. Visit `http://localhost:8080` and log in as the seeded admin.

> Changes to `Moodle-plugin-dev` are tracked separately and require explicit
> approval before they are made.

## Option B — link into an existing Moodle

```bash
scripts/link-into-moodle-dev.sh /path/to/moodle
php /path/to/moodle/admin/cli/upgrade.php --non-interactive
```

## Building JavaScript

The AMD sources are authored as classic `define()` modules. For development:

```bash
scripts/build-js.sh
```

For production, build with Moodle's grunt where possible (the script auto-detects
it). Built files live in `amd/build/*.min.js` and are committed.

## Phase 1B: RDKit WASM

```bash
scripts/update-rdkit-js.sh          # fetch the pinned RDKit.js/WASM bundle
```

The binaries are git-ignored and bundled into the release ZIP.

## Running checks

```bash
scripts/run-local-ci.sh             # moodle-plugin-ci checks if installed
php admin/tool/phpunit/cli/init.php  # (in Moodle) initialise PHPUnit
vendor/bin/phpunit --filter local_chemillusion
```

## Smoke-test checklist (mirrors the PRD)

1. Install/upgrade `local_chemillusion`.
2. Configure local-only mode.
3. PubChem lookup for caffeine.
4. Create a molecule deck.
5. Enable RDKit WASM mode.
6. Render aspirin SVG locally.
7. Detect ester + carboxylic acid groups.
8. Generate a highlighted-group flashcard.
9. Enable ChemIllusion account linking.
10. Click a student CTA and complete the link flow.
11. Click a teacher demo CTA.
12. (Harness) run a `chemillusiondev` LTI launch.
13. (Harness) run a deep-linking smoke test.
14. (Harness) run a public-tool/MCP health check.
