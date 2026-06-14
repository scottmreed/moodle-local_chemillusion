#!/usr/bin/env bash
#
# Fetch the pinned RDKit.js / RDKit WASM bundle into thirdparty/rdkit-js/.
# These binaries are not committed to git; they ship in the release ZIP.
#
# Usage:
#   scripts/update-rdkit-js.sh [version]
# If no version is given, the version pinned in thirdparty/rdkit-js/VERSION.txt
# is used.
#
set -euo pipefail

here="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
dest="${here}/thirdparty/rdkit-js"

version="${1:-}"
if [ -z "${version}" ]; then
  version="$(grep -E '^version:' "${dest}/VERSION.txt" | head -1 | awk '{print $2}')"
fi
if [ -z "${version}" ]; then
  echo "ERROR: no RDKit.js version specified or pinned in VERSION.txt" >&2
  exit 1
fi

base="https://unpkg.com/@rdkit/rdkit@${version}/dist"
echo "Fetching RDKit.js ${version} from ${base} ..."

curl -fsSL "${base}/RDKit_minimal.js"   -o "${dest}/RDKit_minimal.js"
curl -fsSL "${base}/RDKit_minimal.wasm" -o "${dest}/RDKit_minimal.wasm"

js_size=$(wc -c < "${dest}/RDKit_minimal.js")
wasm_size=$(wc -c < "${dest}/RDKit_minimal.wasm")
echo "Downloaded RDKit_minimal.js (${js_size} bytes) and RDKit_minimal.wasm (${wasm_size} bytes)."

if [ "${wasm_size}" -lt 100000 ]; then
  echo "WARNING: RDKit_minimal.wasm looks too small; verify the download." >&2
fi

echo "Remember to keep VERSION.txt and thirdpartylibs.xml in sync (version ${version})."
