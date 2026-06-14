# Contributing to `local_chemillusion`

Thanks for your interest in improving ChemIllusion Study Cards for Moodle. This
repository is intended to remain suitable for a public GitHub project and a
Moodle Plugins Directory submission.

## Ground rules

- All Moodle-facing plugin code is **GPL-3.0-or-later**. By contributing you
  agree your contribution is licensed under those terms.
- Do **not** copy private ChemIllusion internals into this repo (Ketcher overlay,
  full agent harness, MCP private tool inventory, image/video generation
  internals, billing, or production model routing). See `docs/license-boundary.md`.
- Prefer copying **concepts and contracts** over implementation code.
- Do not commit credentials, private URLs, customer data, generated local
  config, logs, database dumps, Moodle site backups, or test accounts.
- Keep all external-service behavior optional and admin-configurable. The plugin
  must be installable without a parallel ChemIllusion server.

## Coding standards

- Follow the [Moodle coding style](https://moodledev.io/general/development/policies/codingstyle).
- PHP files carry the GPL boilerplate header and a `@package local_chemillusion` docblock.
- Namespaced classes live under `classes/` (autoloaded; no `require` needed).
- All user-facing strings live in `lang/en/local_chemillusion.php`.
- Escape all output; use `required_param()` / `optional_param()`; protect
  state-changing actions with `sesskey`.
- Use Moodle DML placeholders for custom SQL and Moodle context/capability
  checks before displaying data or taking an action.
- Store plugin settings via `local_chemillusion/settingname` and
  `get_config('local_chemillusion', ...)`, not global `$CFG` settings.
- Keep CSS, JavaScript selectors, database tables, capabilities, tasks, and
  classes namespaced with `local_chemillusion`.
- Ship English strings only. Translations should go through Moodle language
  tooling after plugin approval.

## Moodle Plugins Directory readiness

Before submitting a change that affects packaging or public behavior, check the
current Moodle plugin contribution guidance:

- Repository root is the plugin root. Files such as `version.php`, `classes/`,
  `db/`, `lang/`, and `templates/` stay at repository root.
- The repository name follows Moodle convention: `moodle-local_chemillusion`.
- No Composer, npm, shell, Python, Conda, or native build step may be required
  for an administrator installing the release ZIP.
- Third-party libraries must be GPL-compatible, declared in
  `thirdpartylibs.xml`, documented in `THIRD_PARTY.md`, and reproducible from
  upstream release sources.
- External integrations must be documented in `README.md` and
  `docs/privacy-and-data-flow.md`, including what data is sent and how admins
  disable it.
- Privacy API metadata must stay accurate whenever stored data or external
  data flows change.

## Before opening a PR

```bash
scripts/run-local-ci.sh     # runs moodle-plugin-ci checks if available
```

- Add or update PHPUnit tests under `tests/phpunit/` and Behat features under `tests/behat/`.
- Run at least PHP syntax checks for changed PHP files if Moodle Plugin CI is
  unavailable locally.
- Update `CHANGELOG.md` under **Unreleased**.
- One feature per branch/PR; keep backend and frontend for a feature together.
- Review `docs/moodle-directory-submission.md` for reviewer-sensitive items.

## Reporting bugs / security

- Functional bugs: open a GitHub issue.
- Security issues: see `SECURITY.md` (please do not file public issues for vulnerabilities).
