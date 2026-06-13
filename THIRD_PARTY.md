# Third-party libraries

This plugin bundles one third-party library for its Phase 1B local chemistry
mode. The canonical machine-readable manifest is `thirdpartylibs.xml`.

## RDKit.js (RDKit WASM)

- **Name:** RDKit.js (`@rdkit/rdkit`)
- **Upstream:** https://github.com/rdkit/rdkit-js and https://www.rdkit.org/
- **License:** BSD-3-Clause (GPL-compatible) — see `thirdparty/rdkit-js/LICENSE`
- **Bundled files (fetched, not committed):**
  - `thirdparty/rdkit-js/RDKit_minimal.js` (loader/bundle)
  - `thirdparty/rdkit-js/RDKit_minimal.wasm` (WebAssembly binary)
- **Version pin:** recorded in `thirdparty/rdkit-js/VERSION.txt`
- **How it is obtained / updated:** `scripts/update-rdkit-js.sh` downloads the
  pinned release from the npm CDN, verifies it, and writes `VERSION.txt`.
- **Generated/minified:** Yes. The `.js` is a minified loader and the `.wasm` is
  a compiled binary; both are produced upstream, not edited here.
- **Why WASM and not native RDKit / `rdkit-php`:** keeps a simple, shell-free,
  Composer-free ZIP install that runs chemistry in the user's browser. See
  `docs/rdkit-wasm-packaging.md`.

### Loading model

RDKit is **lazy-loaded** only on ChemIllusion study tool pages, never on every
Moodle page, and the plugin degrades gracefully to the Phase 1A text/PubChem
experience if it fails to load. No external CDN is required at runtime — assets
are served from the plugin.

## Data source (runtime API, not bundled)

- **PubChem PUG REST** (https://pubchem.ncbi.nlm.nih.gov/) is queried
  server-side only when the admin enables PubChem lookup. Responses are cached
  in `{local_chemillusion_cache}`. PubChem data is in the public domain.
