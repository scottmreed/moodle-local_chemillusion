#!/usr/bin/env bash
#
# Symlink (or copy) this repo into a local Moodle so it appears at
# local/chemillusion. Intended for use with the scottmreed/Moodle-plugin-dev
# harness, which mounts ../moodle-local_chemillusion at
# /var/www/html/local/chemillusion.
#
# Usage:
#   scripts/link-into-moodle-dev.sh /path/to/moodle
#
set -euo pipefail

here="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
moodle="${1:-}"

if [ -z "${moodle}" ] || [ ! -d "${moodle}" ]; then
  echo "Usage: scripts/link-into-moodle-dev.sh /path/to/moodle" >&2
  exit 1
fi

target="${moodle}/local/chemillusion"
if [ -e "${target}" ]; then
  echo "ERROR: ${target} already exists. Remove it first." >&2
  exit 1
fi

ln -s "${here}" "${target}"
echo "Linked ${here} -> ${target}"
echo "Now run the Moodle upgrade (admin/cli/upgrade.php) to install the plugin."
