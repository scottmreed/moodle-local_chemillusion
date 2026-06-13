# Contributing to `local_chemillusion`

Thanks for your interest in improving ChemIllusion Study Cards for Moodle.

## Ground rules

- All Moodle-facing plugin code is **GPL-3.0-or-later**. By contributing you
  agree your contribution is licensed under those terms.
- Do **not** copy private ChemIllusion internals into this repo (Ketcher overlay,
  full agent harness, MCP private tool inventory, image/video generation
  internals, billing, or production model routing). See `docs/license-boundary.md`.
- Prefer copying **concepts and contracts** over implementation code.

## Coding standards

- Follow the [Moodle coding style](https://moodledev.io/general/development/policies/codingstyle).
- PHP files carry the GPL boilerplate header and a `@package local_chemillusion` docblock.
- Namespaced classes live under `classes/` (autoloaded; no `require` needed).
- All user-facing strings live in `lang/en/local_chemillusion.php`.
- Escape all output; use `required_param()` / `optional_param()`; protect
  state-changing actions with `sesskey`.

## Before opening a PR

```bash
scripts/run-local-ci.sh     # runs moodle-plugin-ci checks if available
```

- Add or update PHPUnit tests under `tests/phpunit/` and Behat features under `tests/behat/`.
- Update `CHANGELOG.md` under **Unreleased**.
- One feature per branch/PR; keep backend and frontend for a feature together.

## Reporting bugs / security

- Functional bugs: open a GitHub issue.
- Security issues: see `SECURITY.md` (please do not file public issues for vulnerabilities).
