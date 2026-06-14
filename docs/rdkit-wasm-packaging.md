# RDKit WASM packaging

## Why RDKit.js / WASM

Phase 1B runs chemistry in the **user's browser** via RDKit.js (WASM). The Moodle
server only serves static JS/WASM assets and stores outputs the user explicitly
saves. This avoids the problems of native RDKit (platform binaries, glibc/arch
issues, large ZIPs, binary-blob security review, shared-hosting incompatibility)
and of `rdkit-php` (Composer + native shared library), which conflict with a
shell-free, directory-friendly ZIP install.

## Files

```text
thirdparty/rdkit-js/
  LICENSE                 # BSD-3-Clause (committed)
  VERSION.txt             # pinned version + provenance (committed)
  README.chemillusion.md  # notes (committed)
  RDKit_minimal.js        # fetched, not committed
  RDKit_minimal.wasm      # fetched, not committed
```

The repository-root `thirdpartylibs.xml` declares the library to Moodle.

## Fetch / update

```bash
scripts/update-rdkit-js.sh            # uses VERSION.txt
scripts/update-rdkit-js.sh 2024.09.6  # pin a specific version
```

When changing the version, update **both** `VERSION.txt` and `thirdpartylibs.xml`.

## Loading model

- `amd/src/rdkit_loader.js` resolves the plugin static URLs, initialises RDKit
  once, exposes ready/error state, and times out gracefully.
- RDKit is loaded **only** on ChemIllusion study tool pages (e.g. `tools.php`),
  never on every Moodle page.
- On failure, the UI degrades to the Phase 1A text/PubChem experience.
- **No external CDN at runtime** — assets are served from the plugin.

## Release ZIP

`scripts/package-plugin.sh` includes the fetched binaries (run
`update-rdkit-js.sh` first). Keep an eye on ZIP size; use the minimal RDKit build
and rely on lazy loading.
