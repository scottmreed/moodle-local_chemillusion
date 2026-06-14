#!/usr/bin/env bash
#
# Build the plugin's AMD JavaScript.
#
# Preferred path: run Moodle's grunt from a full Moodle checkout that contains
# this plugin at local/chemillusion. If grunt is unavailable, fall back to
# copying the classic-AMD sources verbatim into amd/build/*.min.js (the sources
# in this repo are authored as plain AMD define() modules, so this is valid).
#
set -euo pipefail

here="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
src="${here}/amd/src"
build="${here}/amd/build"
mkdir -p "${build}"

if command -v grunt >/dev/null 2>&1 && [ -f "${here}/../../Gruntfile.js" ]; then
  echo "Running Moodle grunt amd for local_chemillusion ..."
  ( cd "${here}/../.." && grunt amd --root=local/chemillusion )
  exit 0
fi

echo "grunt not found; copying classic-AMD sources to build/ (dev fallback)."
for f in "${src}"/*.js; do
  name="$(basename "${f}" .js)"
  cp "${f}" "${build}/${name}.min.js"
  echo "  ${name}.js -> build/${name}.min.js"
done
echo "Done. For production releases, build with Moodle grunt where possible."
