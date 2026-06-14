# License boundary

## This repository is GPL-3.0-or-later

All Moodle-facing plugin code here is licensed under GPL-3.0-or-later, matching
Moodle and the Moodle Plugins Directory requirements.

## ChemIllusion proprietary boundary

`chem-art-generator` (the private ChemIllusion SaaS) is **not** open source. The
following must **never** be copied into this repository:

- the Ketcher overlay / generator workspace internals,
- the full agent harness or agentic drawing commands,
- the private MCP tool inventory / descriptors,
- image/video generation internals and budget logic,
- billing, subscription, or production model-routing logic,
- private prompts or secrets.

## What may be adapted

Code may be adapted from `chem-art-generator` only if **all** of the following hold:

1. MolLogic / Scott owns the code.
2. It is intentionally relicensed under GPL for this repo.
3. It contains none of the proprietary items above.
4. It is a small, clean utility, type/schema, API client, or UI concept — not a
   core proprietary implementation.

> Prefer copying **concepts and contracts** over implementation code.

## Practical consequences

- The plugin talks to ChemIllusion only through **narrow public contracts**
  (link/launch URLs and, optionally, public tool endpoints documented in the PRD).
- The signing secret stays server-side and is never emitted to the browser.
- Third-party bundled libraries (RDKit.js) must be GPL-compatible; RDKit.js is
  BSD-3-Clause. See `THIRD_PARTY.md` and `thirdpartylibs.xml`.
