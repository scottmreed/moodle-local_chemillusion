# Release checklist

1. **Version bump** — update `$plugin->version` and `$plugin->release` in
   `version.php`; move `CHANGELOG.md` items from *Unreleased* to the new version.
2. **Third-party sync** — if RDKit.js changed, update `thirdparty/rdkit-js/VERSION.txt`
   and `thirdpartylibs.xml`.
3. **Fetch RDKit** — `scripts/update-rdkit-js.sh`. **Required before packaging** — `scripts/package-plugin.sh` will fail hard if these assets are missing. Pass `--without-rdkit` only for a local-only test build.
4. **Build JS** — `scripts/build-js.sh` (or Moodle grunt). Confirm `amd/build/*.min.js`.
5. **Local CI** — `scripts/run-local-ci.sh` (phplint, codechecker, mustache, grunt, phpunit, behat).
6. **Smoke test** — install the ZIP into the `Moodle-plugin-dev` harness and run
   the smoke-test checklist in `docs/local-dev-testing.md`.
7. **Package** — `scripts/package-plugin.sh --version X.Y.Z --moodle moodle45`.
8. **Verify ZIP** — single root folder `chemillusion`; installs cleanly; size is reasonable.
9. **Tag** — `git tag vX.Y.Z && git push --tags` (triggers `package-release.yml`).
10. **Publish** — attach the ZIP to the GitHub release; update docs/screenshots.

## Acceptance gates

- Phase 1A: installs from ZIP; GPL/README/privacy present; admin can select
  local-only and toggle PubChem; lookups resolve; teacher can create a deck;
  student can study locally; account-link CTA produces signed state; no gradebook.
- Phase 1B: RDKit.js bundled with license + `thirdpartylibs.xml`; lazy-loads on
  tool pages only; renders ≥20 common molecules; validates good/bad SMILES;
  detects the curated functional-group set; highlighted flashcards are accessible;
  degrades gracefully; no runtime CDN; ZIP installs cleanly.

## Supported Moodle range

- Supported: 4.3 through 5.0.x (`$plugin->supported = [403, 500]`)
- Automated CI: Moodle 4.3 and 4.5
- Local dev/testing: Moodle 5.0.8
- Do not claim automated CI on 5.0 unless a 5.0 CI job is added and passing
