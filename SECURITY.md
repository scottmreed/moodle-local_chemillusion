# Security Policy

## Supported versions

The plugin is in alpha (0.2.x). Security fixes target the latest tagged release
and `main`.

## Reporting a vulnerability

Please **do not** open a public GitHub issue for security vulnerabilities.

Instead, use **GitHub's private vulnerability reporting** (Security → Report a
vulnerability) on this repository, or contact the maintainer privately. Include:

- a description of the issue and its impact,
- steps to reproduce or a proof of concept,
- affected version(s) and environment.

We aim to acknowledge reports within a few business days.

## Scope notes specific to this plugin

- The ChemIllusion launch/link **signing secret** must never appear in page
  output or JavaScript. Reports of secret leakage are high priority.
- Browser-generated card payloads (Phase 1B) are treated as **untrusted** and
  sanitised server-side before storage; report any bypass.
- The plugin must never send grades, rosters, raw prompts, or student responses
  to ChemIllusion. Report any data-flow that does.
