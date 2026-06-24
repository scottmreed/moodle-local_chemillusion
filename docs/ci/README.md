# CI workflows

GitHub Actions workflows live in [`.github/workflows/`](../../.github/workflows/):

- **`moodle-plugin-ci.yml`** — lint, codechecker, phpunit, and behat on push/PR (Moodle 4.3 and 4.5)
- **`package-release.yml`** — builds the installable ZIP on `v*` tags and attaches it to the GitHub Release

Local equivalent:

```bash
scripts/run-local-ci.sh
```
