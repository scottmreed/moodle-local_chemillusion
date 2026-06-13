# RDKit.js bundle (ChemIllusion notes)

This directory holds the RDKit.js / RDKit WASM build that `local_chemillusion`
lazy-loads for Phase 1B browser-side chemistry.

## What lives here

| File | Committed? | Purpose |
|------|-----------|---------|
| `LICENSE` | yes | RDKit BSD-3-Clause license text |
| `VERSION.txt` | yes | Pinned version + provenance |
| `README.chemillusion.md` | yes | This file |
| `RDKit_minimal.js` | **no** (fetched) | RDKit loader/bundle |
| `RDKit_minimal.wasm` | **no** (fetched) | RDKit WebAssembly binary |

The two binary assets are intentionally **not** committed to git (they are large
and generated). They are fetched by `scripts/update-rdkit-js.sh` and included in
the **release ZIP** produced by `scripts/package-plugin.sh`.

## Fetching the bundle

```bash
scripts/update-rdkit-js.sh            # uses the version pinned in VERSION.txt
scripts/update-rdkit-js.sh 2024.09.6  # or pin a specific version
```

## Runtime contract

- Loaded only on ChemIllusion study tool pages, never globally.
- Served from this plugin; **no external CDN at runtime**.
- If loading fails, the plugin falls back to Phase 1A text/PubChem mode.

Keep `VERSION.txt` and the repository-root `thirdpartylibs.xml` in sync whenever
the version changes.
