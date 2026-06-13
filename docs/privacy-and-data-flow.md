# Privacy & data flow

## Defaults

A fresh install is **local-only**: study tools work inside Moodle, PubChem
lookup is optional, and ChemIllusion account linking is **off** until an admin
enables it. Admins can disable **every** external call with one switch
(`disable_external`).

## What is stored locally

| Table | Contents | Notes |
|------|----------|-------|
| `local_chemillusion_links` | Moodle userid ↔ opaque ChemIllusion id, optional email hash, timestamps, status | Minimal mapping only |
| `local_chemillusion_cache` | Public PubChem payloads | Keyed by identifier hash; no per-student history |
| `local_chemillusion_events` | Coarse counters (lookups, decks, CTA clicks) | `userid` omitted in minimal mode |
| `local_chemillusion_decks` / `_cards` | User-created study decks/cards | Browser payloads sanitised before storage |

We do **not** store grades, rosters, full Moodle profiles, raw prompts, or
student deck responses.

## What may leave Moodle

### PubChem (server-side, if enabled)
Only the identifier the user searched is sent to PubChem to resolve metadata.

### ChemIllusion account link / launch (user-initiated, if enabled)
A signed, time-limited token carrying **PII-free** source metadata:

```json
{ "source": "moodle", "plugin_component": "local_chemillusion",
  "plugin_version": "0.1.0", "site_hash": "…", "role": "student|teacher|admin",
  "surface": "study_card|molecule_lookup|teacher_dashboard",
  "cta": "save_deck|visual_card|teacher_demo" }
```

We never send: raw Moodle profile, full course content, grades, student deck
responses, or raw prompts.

### Conversion metadata (consent-gated)
Only if the admin enables `enable_conversion_metadata`, the same PII-free shape
may be POSTed to the ChemIllusion public API when a user clicks a funnel CTA.

## Privacy API

`classes/privacy/provider.php` implements the Moodle Privacy API for the link,
deck, and card tables and declares the external link to ChemIllusion. Users' data
can be exported and deleted. A human-readable summary is available in-product at
`privacy.php`.
