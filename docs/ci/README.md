# CI workflows (manual placement required)

The two YAML files in this folder are the intended GitHub Actions workflows:

- `moodle-plugin-ci.yml` → move to `.github/workflows/moodle-plugin-ci.yml`
- `package-release.yml` → move to `.github/workflows/package-release.yml`

They live here temporarily because the automation token that bootstrapped this
repository does not hold the GitHub `workflow` OAuth scope, so it could not write
under `.github/workflows/`. Move them into `.github/workflows/` with an account
that has the `workflow` scope (a normal `git mv` + push works):

```bash
mkdir -p .github/workflows
git mv docs/ci/moodle-plugin-ci.yml .github/workflows/moodle-plugin-ci.yml
git mv docs/ci/package-release.yml .github/workflows/package-release.yml
git commit -m "ci: enable Moodle Plugin CI and release packaging workflows"
git push
```

Once moved, `moodle-plugin-ci.yml` runs lint/codechecker/phpunit/behat on push
and PRs, and `package-release.yml` builds the installable ZIP on version tags.
