#!/usr/bin/env bash
#
# Build a Moodle-installable release ZIP.
#
# Usage:
#   scripts/package-plugin.sh --version 0.1.0 [--moodle moodle45] [--without-rdkit]
#
# Produces: dist/local_chemillusion-<version>-<moodle>.zip
# The ZIP contains a single root folder "chemillusion" (so it installs to
# local/chemillusion).
#
# By default, packaging expects RDKit assets to be present. Run
# scripts/update-rdkit-js.sh first if they are missing.
# Pass --without-rdkit for a local-only test package (RDKit will be absent
# from the ZIP and enable_rdkit should default to 0 in your settings.php).
#
set -euo pipefail

here="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
version=""
moodle="moodle45"
include_rdkit=1

while [ $# -gt 0 ]; do
  case "$1" in
    --version)      version="$2";  shift 2;;
    --moodle)       moodle="$2";   shift 2;;
    --without-rdkit) include_rdkit=0; shift 1;;
    *) echo "Unknown arg: $1" >&2; exit 1;;
  esac
done

if [ -z "${version}" ]; then
  echo "ERROR: --version is required" >&2
  exit 1
fi

if [ "${include_rdkit}" -eq 1 ]; then
  missing=0
  for asset in RDKit_minimal.js RDKit_minimal.wasm LICENSE VERSION.txt; do
    if [ ! -f "${here}/thirdparty/rdkit-js/${asset}" ]; then
      echo "ERROR: missing thirdparty/rdkit-js/${asset}" >&2
      missing=1
    fi
  done
  if [ "${missing}" -eq 1 ]; then
    echo "" >&2
    echo "RDKit Phase 1B assets are missing." >&2
    echo "Run scripts/update-rdkit-js.sh before packaging, or pass --without-rdkit" >&2
    echo "for a local-only package (RDKit rendering will be unavailable)." >&2
    exit 1
  fi
else
  echo "WARNING: Building without RDKit assets. RDKit rendering will be unavailable in this ZIP." >&2
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
  --exclude '.env' \
  --exclude '*.env' \
  --exclude 'testing' \
  --exclude '.superpowers' \
  "${here}/" "${root}/"

# Build JS into amd/build if needed.
if [ ! -d "${root}/amd/build" ] || [ -z "$(ls -A "${root}/amd/build" 2>/dev/null)" ]; then
  echo "amd/build missing; running build-js.sh ..."
  ( cd "${here}" && bash scripts/build-js.sh )
  rsync -a "${here}/amd/build/" "${root}/amd/build/"
fi

# Strip RDKit binaries if --without-rdkit was passed.
if [ "${include_rdkit}" -eq 0 ]; then
  rm -f "${root}/thirdparty/rdkit-js/RDKit_minimal.js"
  rm -f "${root}/thirdparty/rdkit-js/RDKit_minimal.wasm"
fi

out="${dist}/local_chemillusion-${version}-${moodle}.zip"
rm -f "${out}"
( cd "${stage}" && zip -r -q "${out}" chemillusion )
rm -rf "${stage}"

echo "Built ${out}"
