#!/usr/bin/env bash
#
# Run a subset of Moodle Plugin CI checks locally, if moodle-plugin-ci is on PATH.
# This mirrors the GitHub Actions workflow so contributors can pre-flight a PR.
#
set -euo pipefail

here="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

if ! command -v moodle-plugin-ci >/dev/null 2>&1; then
  cat >&2 <<'EOF'
moodle-plugin-ci not found.

Install it (see https://moodlehq.github.io/moodle-plugin-ci/) or rely on the
GitHub Actions workflow in .github/workflows/moodle-plugin-ci.yml.

Quick local sanity checks you can still run:
  php -l version.php
  find . -name '*.php' -print0 | xargs -0 -n1 php -l
EOF
  exit 0
fi

echo "== PHP lint =="
moodle-plugin-ci phplint "${here}"
echo "== PHP mess detector =="
moodle-plugin-ci phpmd "${here}" || true
echo "== Moodle code checker =="
moodle-plugin-ci codechecker "${here}" || true
echo "== Mustache lint =="
moodle-plugin-ci mustache "${here}" || true
echo "== Grunt =="
moodle-plugin-ci grunt "${here}" || true
echo "== PHPUnit =="
moodle-plugin-ci phpunit "${here}" || true
echo "Done."
