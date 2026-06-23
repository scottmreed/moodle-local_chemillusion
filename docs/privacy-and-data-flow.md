# Privacy & data flow

## Defaults

A fresh install is **local-only**: study tools work inside Moodle, PubChem
lookup is optional, and ChemIllusion account linking is **off** until an admin
enables it. Admins can disable **every** external call with one switch
(`disable_external`).

## No subscription required for local use

**ChemIllusion Study Tools works locally without a ChemIllusion subscription.**
Optional account linking / SaaS escalation requires a ChemIllusion account and
may require a paid ChemIllusion plan. Review/demo credentials are available on
request.

## What is stored locally

| Table | Contents | Notes |
|------|----------|-------|
| `local_chemillusion_links` | Moodle userid ↔ opaque ChemIllusion id, optional email hash, timestamps, status | Minimal mapping only |
| `local_chemillusion_cache` | Public PubChem payloads | Keyed by identifier hash; no per-student history |
| `local_chemillusion_events` | Coarse usage events (lookups, decks, CTA clicks) | `userid` omitted in minimal mode; declared in Privacy API |
| `local_chemillusion_decks` / `_cards` | User-created study decks/cards | Browser payloads sanitised before storage |

We do **not** store grades, rosters, full Moodle profiles, raw prompts, or
student deck responses.

## What may leave Moodle

### PubChem (server-side, if enabled)

Only the identifier the user searched is sent to PubChem's public PUG REST API
to resolve molecule metadata. This is declared in the Privacy API as the
`pubchem_pug_rest` external location. Results are cached locally.

### ChemIllusion account link / launch (user-initiated, if enabled)

A signed, time-limited token carrying **PII-free** source metadata:

```json
{ "source": "moodle", "plugin_component": "local_chemillusion",
  "plugin_version": "0.1.0", "site_hash": "…", "role": "student|teacher|admin",
  "surface": "study_card|molecule_lookup|teacher_dashboard",
  "cta": "save_deck|visual_card|teacher_demo" }
```

Optional account linking / SaaS escalation requires a ChemIllusion account and
may require a paid ChemIllusion plan. Local study tools require no ChemIllusion
subscription.

We never send: raw Moodle profile, full course content, grades, student deck
responses, or raw prompts.

### Conversion metadata (consent-gated)

Only if the admin enables `enable_conversion_metadata`, the same PII-free shape
may be POSTed to the ChemIllusion public API when a user clicks a funnel CTA.

## Privacy API

`classes/privacy/provider.php` implements the Moodle Privacy API for:

- `local_chemillusion_links` — account link mapping
- `local_chemillusion_decks` / `local_chemillusion_cards` — user study content
- `local_chemillusion_events` — coarse usage events (when userid is stored)
- `chemillusion_saas` — external location (account link / launch)
- `pubchem_pug_rest` — external location (molecule identifier lookup)

Users' data can be exported and deleted via Moodle's standard privacy tools.
A human-readable summary is available in-product at `privacy.php`.
