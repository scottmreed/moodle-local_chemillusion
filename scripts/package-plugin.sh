#!/usr/bin/env bash
#
# Build a Moodle-installable release ZIP.
#
# Usage:
#   scripts/package-plugin.sh --version 0.1.0 [--moodle moodle45]
#
# Produces: dist/local_chemillusion-<version>-<moodle>.zip
# The ZIP contains a single root folder "chemillusion" (so it installs to
# local/chemillusion).
#
set -euo pipefail

here="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
version=""
moodle="moodle45"

while [ $# -gt 0 ]; do
  case "$1" in
    --version) version="$2"; shift 2;;
    --moodle)  moodle="$2"; shift 2;;
    *) echo "Unknown arg: $1" >&2; exit 1;;
  esac
done

if [ -z "${version}" ]; then
  echo "ERROR: --version is required" >&2
  exit 1
fi

dist="${here}/dist"
stage="$(mktemp -d)"
root="${stage}/chemillusion"
mkdir -p "${root}" "${dist}"

# Copy plugin payload, excluding dev-only and VCS artefacts.
rsync -a \
  --exclude '.git' \
  --exclude 'dist' \
  --exclude 'node_modules' \
  --exclude '.github' \
  --exclude 'scripts' \
  --exclude '*.zip' \
  "${here}/" "${root}/"

# Build JS into amd/build if needed.
if [ ! -d "${root}/amd/build" ] || [ -z "$(ls -A "${root}/amd/build" 2>/dev/null)" ]; then
  echo "amd/build missing; running build-js.sh ..."
  ( cd "${here}" && bash scripts/build-js.sh )
  rsync -a "${here}/amd/build/" "${root}/amd/build/"
fi

# Warn if RDKit binaries are absent (Phase 1B).
if [ ! -f "${root}/thirdparty/rdkit-js/RDKit_minimal.wasm" ]; then
  echo "WARNING: RDKit WASM not present. Run scripts/update-rdkit-js.sh before"
  echo "         packaging if you want Phase 1B in this ZIP." >&2
fi

out="${dist}/local_chemillusion-${version}-${moodle}.zip"
rm -f "${out}"
( cd "${stage}" && zip -r -q "${out}" chemillusion )
rm -rf "${stage}"

echo "Built ${out}"
