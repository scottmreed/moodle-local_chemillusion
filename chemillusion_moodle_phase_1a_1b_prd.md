# PRD: ChemIllusion for Moodle Phase 1A/1B

**Target public repo:** `scottmreed/moodle-local-chemillusion`  
**Production Moodle component:** `local_chemillusion`  
**Local Moodle harness repo:** `scottmreed/Moodle-plugin-dev`  
**Private product repo:** `chem-art-generator`  
**License target for Moodle plugin:** GNU GPL v3.0-or-later  
**Bundled chemistry library target:** RDKit.js / RDKit WASM under BSD-3-Clause, included only if license files, source notes, and build/update steps are documented  
**Status:** Detailed implementation PRD for Phase 1A and Phase 1B  
**Last updated:** 2026-06-13

---

## 1. Executive summary

ChemIllusion for Moodle will be a new FOSS Moodle plugin repository, separate from `chem-art-generator`, designed to make Moodle a high-trust acquisition funnel for ChemIllusion while offering genuinely useful chemistry study tools inside Moodle.

The plugin should **not** be marketed as the first chemical drawing tool for Moodle. Moodle already has chemistry editors and student-drawn-response tools. The stronger and more defensible wedge is:

> A privacy-aware, open-source Moodle chemistry study plugin that combines molecule lookup, RDKit.js-powered browser-side structure rendering, functional-group highlighting, student flashcards, accessible summaries, and optional ChemIllusion account linking for richer AI, image, video, tutorial, and teacher workflows.

The first release is split into two closely related phases:

- **Phase 1A — Directory-safe baseline:** PHP-first `local_chemillusion` plugin with PubChem lookup, study cards, reagent/functional-group dictionaries, account linking, and ChemIllusion funnel CTAs. No RDKit dependency required.
- **Phase 1B — RDKit WASM local chemistry mode:** Bundle RDKit.js/WASM in the Moodle plugin ZIP, lazy-load it only on ChemIllusion pages, and add client-side SMILES validation, SVG rendering, functional-group SMARTS matching, static atom/bond highlighting, and richer study cards.

ChemIllusion SaaS remains private and premium. The Moodle plugin will expose only narrow public contracts and UI clients. The full ChemIllusion Ketcher overlay, full agent harness, MCP private inventory, image/video generation internals, billing, and advanced ChemTutor remain in `chem-art-generator`.

---

## 2. Product goals

### 2.1 Student goals

1. Give students a useful free chemistry study tool inside Moodle.
2. Let students search a molecule by name, SMILES, InChI, or InChIKey.
3. Let students generate flashcards from molecules, functional groups, and reagents.
4. Let students see structures, highlighted functional groups, and basic property summaries without a paid account.
5. Give students a low-friction path to create or link a ChemIllusion account when they want richer tools.
6. Give students a visible taste of ChemIllusion image/visual study capabilities without exposing private image-generation systems.

### 2.2 Teacher goals

1. Give teachers a Moodle-native chemistry study-card tool they can recommend without forcing immediate purchase.
2. Let teachers create course-specific molecule decks, reagent decks, and functional-group decks.
3. Let teachers generate Moodle-ready resource cards from molecules.
4. Let teachers test ChemIllusion from inside Moodle and request a pilot/demo.
5. Make teacher adoption feel like a low-risk FOSS utility first, not a vendor lock-in.

### 2.3 Business goals

1. Turn Moodle usage into individual student account signups.
2. Turn teacher usage into ChemIllusion pilots, demos, and class/institution adoption.
3. Build international trust by offering a GPL Moodle plugin with local-only mode.
4. Preserve ChemIllusion’s product moat by keeping advanced SaaS features private.
5. Use RDKit WASM to make the free plugin valuable enough to spread organically.

---

## 3. Non-goals

- Do not port the full ChemIllusion React app into Moodle.
- Do not FOSS the full Ketcher overlay or generator workspace.
- Do not bundle server-side Python RDKit or native RDKit libraries.
- Do not require Composer, shell access, Conda, Python, or native packages for normal install.
- Do not expose the full MCP private tool inventory.
- Do not include gradebook passback in Phase 1A/1B.
- Do not create a production `mod_chemillusion` activity in Phase 1A/1B.
- Do not store grades or detailed AI/tool traces in Moodle.
- Do not send student prompt/answer data to ChemIllusion by default.

---

## 4. Core decisions

| Area | Decision |
|---|---|
| Production plugin type | Start with `local_chemillusion` only. |
| Repo | Build in `scottmreed/moodle-local-chemillusion`. |
| License | GPL-3.0-or-later for Moodle-facing plugin code. |
| RDKit approach | Use RDKit.js/WASM in Phase 1B; no native RDKit bundling. |
| Install model | Plugin must install from ZIP with no admin-run Composer or shell commands. |
| ChemIllusion proprietary boundary | Do not copy private Ketcher overlay, full harness, image/video tools, billing, or private MCP internals into the FOSS repo. |
| Student data | Store minimal local mappings only; no grades; detailed SaaS traces only after explicit user opt-in. |
| Funnel | Free local study tool first; account linking and ChemIllusion CTAs throughout. |
| BYOK | Not a standalone Moodle provider framework in Phase 1. Route richer AI through ChemIllusion account/OpenRouter BYOK path later. |
| Testing | Use `scottmreed/Moodle-plugin-dev` as the local LMS/dev harness. |

---

## 5. Why RDKit WASM instead of native RDKit or `rdkit-php`

### 5.1 Native RDKit is not appropriate for Phase 1

Bundling native RDKit libraries would create install, platform, and review problems:

- platform-specific Linux/macOS/Windows binaries
- glibc and architecture compatibility issues
- large plugin ZIP
- security review burden around binary blobs
- MoodleCloud/shared-hosting incompatibility
- update/CVE burden

### 5.2 `rdkit-php` is useful but not baseline-safe

`rdkit-php` is promising for controlled/self-hosted environments, but it normally uses Composer and a native shared RDKit library. That conflicts with the Moodle-directory-friendly goal of a simple ZIP install with no shell or Composer requirement.

Use `rdkit-php` only as a possible future optional advanced mode, not as Phase 1A/1B baseline.

### 5.3 RDKit WASM is the best local-FOSS option

RDKit.js lets the plugin run chemistry functionality in the user’s browser. The Moodle server only serves static JS/WASM assets and receives saved outputs when the user explicitly submits them.

Phase 1B should use RDKit.js/WASM for:

- SMILES validation
- canonicalization where supported
- 2D SVG rendering
- functional-group SMARTS matching
- atom and bond highlighting in static SVG
- basic descriptors exposed by RDKit.js
- local flashcard generation

---

## 6. License and packaging strategy

### 6.1 Moodle plugin code

All files that implement the Moodle plugin interface must be GPL-3.0-or-later.

Add:

```text
LICENSE                         # GPL-3.0-or-later
README.md
THIRD_PARTY.md
thirdpartylibs.xml
```

### 6.2 RDKit.js/WASM license alignment

RDKit.js and RDKit are BSD-style permissive projects. BSD-3-Clause is generally GPL-compatible. The plugin must include:

```text
thirdparty/rdkit-js/LICENSE
thirdparty/rdkit-js/README.md or VERSION.txt
thirdpartylibs.xml
scripts/update-rdkit-js.sh
```

`thirdpartylibs.xml` should document:

- library name: RDKit.js
- version
- source URL
- license: BSD-3-Clause
- bundled files: JS loader/bundle and `.wasm`
- update/build procedure
- whether files are minified/generated

### 6.3 No proprietary code leakage

Code may be adapted from `chem-art-generator` only if:

1. MolLogic/Scott owns the code.
2. The copied code is intentionally relicensed under GPL for the Moodle repo.
3. It does not include private prompts, secrets, billing logic, production model routing, private MCP tool descriptors, or Ketcher overlay/workspace internals.
4. It is a clean small utility, type/schema, API client, or UI concept rather than a core proprietary implementation.

Prefer copying **concepts and contracts** over copying implementation code.

### 6.4 ZIP install strategy

Release ZIP must install without Composer or build steps.

Release artifact should contain built JS files:

```text
amd/build/*.min.js
thirdparty/rdkit-js/RDKit_minimal.js
thirdparty/rdkit-js/RDKit_minimal.wasm
```

The development repo can use npm/build tools, but the Moodle admin should only upload the ZIP.

Recommended package command:

```bash
scripts/package-plugin.sh --version 0.1.0
```

Expected output:

```text
dist/local_chemillusion-0.1.0-moodle45.zip
```

The ZIP should contain a single root plugin folder or a directory structure accepted by Moodle’s plugin installer. Validate through the local Moodle dev harness before release.

---

## 7. Phase 1A: Directory-safe baseline

### 7.1 Phase 1A goal

Ship a clean Moodle plugin that is useful without RDKit WASM or ChemIllusion SaaS access.

### 7.2 Phase 1A capabilities

#### A. Admin settings

Settings page under Site administration:

```text
ChemIllusion mode:
  - Local-only
  - Local + ChemIllusion account linking
  - Local + ChemIllusion SaaS tools

External services:
  - Enable PubChem lookup
  - Enable ChemIllusion account linking
  - Enable ChemIllusion visual preview examples
  - Enable external analytics/conversion metadata

Privacy:
  - Minimal mode default
  - Show data-flow summary
  - Disable all external calls
```

Acceptance:

- settings saved in `config_plugins`
- no secrets exposed in page output
- admin can disable every external call
- data-flow summary visible before enabling ChemIllusion SaaS

#### B. PubChem lookup

User enters:

```text
name | SMILES | InChI | InChIKey
```

Plugin returns:

```text
preferred name
PubChem CID
formula
molecular weight
canonical SMILES if available
isomeric SMILES if available
InChIKey
PubChem link
Open in ChemIllusion button
```

Implementation:

- PHP PubChem client using Moodle HTTP/cURL utilities
- server-side cache table for public lookup payloads
- no per-student query history by default
- visible error states for no match/rate limit/network failure

#### C. Molecule resource cards

Teacher can convert a lookup result into a Moodle resource card:

```text
Title: Aspirin
Structure placeholder or PubChem link
Formula: C9H8O4
MW: 180.16
CID: 2244
Canonical SMILES: CC(=O)Oc1ccccc1C(=O)O
Study actions:
  - Copy card
  - Add to deck
  - Open in ChemIllusion
```

Phase 1A card can use text-only or PubChem-linked structure image if safe. Phase 1B replaces placeholder with local RDKit SVG.

#### D. Study deck builder

Teacher creates a deck from molecules/reagents/functional groups.

Deck types:

```text
Molecule identity deck
Functional group recognition deck
Reagent acronym deck
Name ↔ SMILES deck
Formula/MW deck
```

Phase 1A cards are mostly text and PubChem-derived metadata. Students can study in Moodle without linking a ChemIllusion account.

#### E. Reagent acronym mini-dictionary

Static JSON data in the plugin:

```json
{
  "PCC": {
    "name": "pyridinium chlorochromate",
    "role": "oxidant",
    "common_use": "oxidizes many alcohols under mild conditions",
    "level": "organic_1"
  }
}
```

Features:

- browse/search reagent acronym
- generate reagent flashcards
- “open in ChemIllusion for deeper explanation” CTA

#### F. Functional group dictionary

Static JSON data for common functional groups:

```json
{
  "alcohol": {
    "label": "Alcohol",
    "smarts": "[OX2H][CX4]",
    "student_summary": "An OH group attached to an sp3 carbon.",
    "level": "organic_1"
  }
}
```

Phase 1A uses this dictionary for cards and explanations. Phase 1B uses the SMARTS patterns in RDKit.js matching.

#### G. Account linking funnel

Unlinked users see:

```text
Continue in ChemIllusion
Save this deck to ChemIllusion
Generate a visual study card in ChemIllusion
Try the guided molecule workspace
```

Account-link flow:

1. Moodle user clicks connect.
2. Plugin creates signed launch/link token.
3. ChemIllusion opens signup/login page with Moodle source metadata.
4. ChemIllusion returns linked account ID or link success token.
5. Plugin stores minimal mapping.

Stored locally:

```text
moodle user id
chemillusion user id
hashed email if needed
linked_at
last_launch_at
status
```

Do not store:

```text
grades
raw prompts
full roster
full Moodle profile
student AI traces
```

#### H. Teacher adoption funnel

Teacher-facing CTAs:

```text
Create a free ChemIllusion teacher account
Book a ChemIllusion demo
Convert this deck into a ChemIllusion guided activity
Generate visual study cards for your class
Try accessible molecule readout
```

Admin/teacher dashboard should include:

```text
number of local molecule lookups
number of decks created
number of student study sessions
number of account-link clicks
number of teacher demo clicks
```

Only collect/send ChemIllusion conversion metadata if admin enables it.

---

## 8. Phase 1B: RDKit WASM local chemistry mode

### 8.1 Phase 1B goal

Bundle RDKit.js/WASM inside the Moodle plugin ZIP and add modern browser-side chemistry study tooling without requiring ChemIllusion SaaS.

### 8.2 Loading model

Do not load RDKit on every Moodle page.

Good:

```text
User opens ChemIllusion molecule tool → load RDKit JS/WASM lazily.
```

Bad:

```text
Every Moodle course page loads RDKit WASM.
```

Implementation:

```text
amd/src/rdkit_loader.js
  - resolves plugin static URL for RDKit_minimal.js and .wasm
  - initializes RDKit once
  - exposes ready/error state
  - times out gracefully
```

### 8.3 Client-side features

#### A. SMILES validation

User enters SMILES; RDKit.js validates in browser.

Outputs:

```text
valid/invalid
canonical SMILES where supported
simple error message
Open in ChemIllusion for repair/help CTA if invalid
```

#### B. SVG rendering

Render molecule SVG from SMILES in browser.

Requirements:

- accessible text alternative shown next to SVG
- SVG not used as the only representation
- copy/download SVG button
- no hidden external calls

#### C. Functional-group detection

Use static functional-group SMARTS registry plus RDKit.js matching.

Output:

```text
Detected functional groups:
- aromatic ring
- carboxylic acid
- ester
```

For each detected group:

```text
label
short student explanation
highlight button
generate flashcard button
open in ChemIllusion button
```

#### D. Static atom/bond highlighting

When a user selects a functional group, display a highlighted SVG.

Do not port ChemIllusion’s Ketcher overlay.

Acceptable:

```text
RDKit.js-highlighted static SVG
simple label overlay outside SVG
```

Not acceptable:

```text
interactive ChemIllusion canvas state
private Ketcher overlay
fragment editor internals
agentic drawing commands
```

#### E. Local descriptor panel

Where supported by RDKit.js, show:

```text
formula
molecular weight
HBD/HBA
TPSA
logP
rotatable bonds
ring count
heavy atom count
```

Label descriptor availability clearly. If a value is not available locally, do not fabricate it.

#### F. RDKit-powered study cards

New card types:

```text
Structure → name/metadata
Highlighted group → identify group
Functional group name → highlighted examples
Molecule → list functional groups
SMILES → rendered structure
Rendered structure → SMILES/name
Property prompt → choose approximate MW/formula
```

Students can study locally. Linking a ChemIllusion account unlocks saving decks, advanced explanations, image cards, and richer accessible descriptions.

---

## 9. Novel student study tooling

The plugin should be framed as **ChemIllusion Study Cards for Moodle** rather than just an admin integration.

### 9.1 Study surfaces

#### Molecule card

Front:

```text
Rendered structure or molecule name
```

Back:

```text
name
formula
MW
CID
canonical SMILES
functional groups
```

#### Functional group card

Front:

```text
Highlighted molecule SVG
Question: Identify the highlighted group.
```

Back:

```text
Alcohol / phenol / ester / amide etc.
Short explanation.
Common mistake.
Open in ChemIllusion for more.
```

#### Reagent card

Front:

```text
PCC
```

Back:

```text
pyridinium chlorochromate
oxidation reagent
common Organic 1/2 use
```

#### Accessibility card

Front:

```text
Structure image
```

Back:

```text
Text summary and detected features.
```

Phase 1A uses templated summaries. Phase 1B adds RDKit-derived features. ChemIllusion account adds richer natural-language readout.

### 9.2 Deck creation flows

Teacher flow:

1. Paste a list of molecule names/SMILES.
2. Resolve through PubChem.
3. Generate local cards.
4. Optionally add functional-group highlights if RDKit WASM is enabled.
5. Export/publish deck inside Moodle.
6. Offer “Upgrade this to a ChemIllusion guided activity.”

Student flow:

1. Open deck from Moodle course.
2. Study cards locally.
3. Save progress locally if enabled.
4. Click “Save to ChemIllusion” or “Generate visual card” to create/link account.

### 9.3 ChemIllusion image-generation taste

Do not run anonymous image generation from Moodle by default.

Phase 1A/1B should include:

- bundled or CDN-hosted static examples of ChemIllusion-style visual chemistry cards
- “Generate this as a visual study card” CTA
- account required before generating a custom image
- optional teacher demo CTA for class-wide visual decks

ChemIllusion-side image generation remains private and billed/limited by existing ChemIllusion account logic.

Suggested CTA copy:

```text
Make this memorable in ChemIllusion
Generate a visual study card with your molecule and functional group highlighted.
```

---

## 10. Privacy and funnel design

### 10.1 Default privacy mode

Default install mode:

```text
Local-only + PubChem lookup optional + no ChemIllusion account linking until admin enables it.
```

When account linking is enabled:

- user initiates the link
- plugin sends minimal signed source metadata
- no grades or rosters are sent
- student study data is not sent by default

### 10.2 Conversion source metadata

With admin/user consent, ChemIllusion may receive:

```json
{
  "source": "moodle",
  "plugin_component": "local_chemillusion",
  "plugin_version": "0.1.0",
  "site_hash": "...",
  "course_hash": "...",
  "role": "student|teacher|admin",
  "surface": "study_card|molecule_lookup|teacher_dashboard",
  "cta": "save_deck|visual_card|teacher_demo"
}
```

Do not send:

```text
raw Moodle user profile
full course content
student grades
student deck responses
raw prompts
```

### 10.3 Student subscription funnel

Student CTAs:

```text
Save my deck
Generate a visual study card
Open this molecule in ChemIllusion
Get pronunciation / ChemLingo
Get an accessible molecule explanation
Practice with a guided tutorial
```

Subscription pitch should be framed as:

```text
Keep studying outside Moodle, save your molecules, generate visual cards, and unlock guided chemistry tools.
```

### 10.4 Teacher adoption funnel

Teacher CTAs:

```text
Create teacher account
Book a demo
Convert deck to a ChemIllusion guided activity
Create class visual card set
Pilot ChemIllusion for Organic 1
```

Teacher dashboard copy:

```text
Your students can use the local Moodle study cards for free. Link a ChemIllusion teacher account to create richer visual decks, guided tutorials, accessible molecule readouts, and course-ready activities.
```

---

## 11. Repository/file breakdown

## 11.1 `scottmreed/moodle-local-chemillusion` — new public FOSS repo

### Root files

```text
README.md
LICENSE
CHANGELOG.md
CONTRIBUTING.md
CODE_OF_CONDUCT.md
SECURITY.md
THIRD_PARTY.md
thirdpartylibs.xml
version.php
settings.php
index.php
classes.php?                  # only if needed; prefer namespaced classes
```

### Moodle metadata/config

```text
db/access.php
db/install.xml
db/upgrade.php
db/services.php
db/tasks.php                  # optional cleanup task for cache/events
lang/en/local_chemillusion.php
```

### Public pages/controllers

```text
index.php                     # landing/dashboard for user/course context
tools.php                     # molecule lookup/study tools
cards.php                     # study deck/card UI
link.php                      # start/complete account linking
launch.php                    # signed ChemIllusion launch helper
privacy.php                   # human-readable privacy summary page if useful
```

### PHP classes

```text
classes/api/chemillusion_client.php
classes/api/pubchem_client.php
classes/auth/account_linker.php
classes/cache/molecule_cache.php
classes/cards/card_generator.php
classes/cards/deck_repository.php
classes/cards/reagent_dictionary.php
classes/cards/functional_group_dictionary.php
classes/external/lookup_molecule.php
classes/external/save_deck.php
classes/form/admin_settings_form.php        # only if custom form needed
classes/form/molecule_lookup_form.php
classes/output/dashboard_page.php
classes/output/molecule_card.php
classes/output/study_deck_page.php
classes/output/renderer.php
classes/privacy/provider.php
classes/security/signed_state.php
classes/telemetry/local_event_logger.php
classes/util/input_normalizer.php
```

### Templates

```text
templates/dashboard.mustache
templates/molecule_lookup.mustache
templates/molecule_result_card.mustache
templates/study_deck.mustache
templates/flashcard.mustache
templates/functional_group_badge.mustache
templates/account_link_cta.mustache
templates/teacher_demo_cta.mustache
templates/privacy_summary.mustache
templates/rdkit_status.mustache
```

### JavaScript source/build

Use Moodle AMD/ESM tooling compatible with targeted Moodle versions.

```text
amd/src/molecule_lookup.js
amd/src/study_deck.js
amd/src/account_link.js
amd/src/rdkit_loader.js               # Phase 1B
amd/src/rdkit_molecule_renderer.js    # Phase 1B
amd/src/functional_group_matcher.js   # Phase 1B
amd/src/flashcard_player.js
amd/build/*.min.js                    # release ZIP includes built files
```

### RDKit WASM bundle — Phase 1B

```text
thirdparty/rdkit-js/LICENSE
thirdparty/rdkit-js/VERSION.txt
thirdparty/rdkit-js/RDKit_minimal.js
thirdparty/rdkit-js/RDKit_minimal.wasm
thirdparty/rdkit-js/README.chemillusion.md
```

If file names differ in the selected RDKit.js release, update paths but keep the same intent.

### Data resources

```text
resources/functional_groups.json
resources/reagents_organic1.json
resources/reagents_organic2.json
resources/example_molecules.json
resources/demo_visual_cards.json
```

Example `functional_groups.json`:

```json
{
  "carboxylic_acid": {
    "label": "Carboxylic acid",
    "smarts": "C(=O)[OH]",
    "level": "organic_1",
    "student_summary": "A carbonyl carbon bonded to an OH group.",
    "common_mistake": "Do not confuse esters with carboxylic acids; esters have OR instead of OH."
  }
}
```

### Tests

```text
tests/phpunit/pubchem_client_test.php
tests/phpunit/molecule_cache_test.php
tests/phpunit/account_linker_test.php
tests/phpunit/privacy_provider_test.php
tests/phpunit/card_generator_test.php
tests/behat/admin_settings.feature
tests/behat/molecule_lookup.feature
tests/behat/study_deck.feature
tests/behat/account_link_cta.feature
tests/behat/rdkit_wasm_cards.feature        # Phase 1B
```

### Tooling/docs

```text
.github/workflows/moodle-plugin-ci.yml
.github/workflows/package-release.yml
scripts/build-js.sh
scripts/update-rdkit-js.sh
scripts/package-plugin.sh
scripts/link-into-moodle-dev.sh
scripts/run-local-ci.sh
docs/local-dev-testing.md
docs/license-boundary.md
docs/rdkit-wasm-packaging.md
docs/privacy-and-data-flow.md
docs/release-checklist.md
docs/moodle-directory-submission.md
```

### Database tables

Phase 1A:

```text
local_chemillusion_links
local_chemillusion_cache
local_chemillusion_events
local_chemillusion_decks
local_chemillusion_cards
```

Suggested minimal schema:

```sql
local_chemillusion_links
  id BIGINT PK
  userid BIGINT NOT NULL
  chemillusion_user_id VARCHAR(128) NULL
  chemillusion_email_hash VARCHAR(128) NULL
  linked_at BIGINT NOT NULL
  last_launch_at BIGINT NULL
  status VARCHAR(32) NOT NULL

local_chemillusion_cache
  id BIGINT PK
  cachekey VARCHAR(255) UNIQUE NOT NULL
  cachename VARCHAR(64) NOT NULL
  payload LONGTEXT NOT NULL
  expires_at BIGINT NOT NULL
  created_at BIGINT NOT NULL

local_chemillusion_decks
  id BIGINT PK
  courseid BIGINT NULL
  userid BIGINT NOT NULL
  name VARCHAR(255) NOT NULL
  visibility VARCHAR(32) NOT NULL
  source VARCHAR(64) NOT NULL
  created_at BIGINT NOT NULL
  updated_at BIGINT NOT NULL

local_chemillusion_cards
  id BIGINT PK
  deckid BIGINT NOT NULL
  cardtype VARCHAR(64) NOT NULL
  prompt LONGTEXT NOT NULL
  answer LONGTEXT NOT NULL
  molecule_payload LONGTEXT NULL
  sortorder BIGINT NOT NULL
  created_at BIGINT NOT NULL
```

Do not store individual student study responses by default. If progress tracking is later added, make it course/admin-controlled and privacy-documented.

---

## 11.2 `scottmreed/Moodle-plugin-dev` — local Moodle harness repo

This repo is not the production plugin. It is the local LMS/dev harness used to prove Moodle behavior before release.

### Files to add/update

```text
README.md                                  # add ChemIllusion local testing section
.env.example                               # Moodle + ChemIllusion local URLs
docker-compose.test.yml                    # Moodle + Postgres + mounted plugin
scripts/start-chemillusion-moodle-dev.sh
scripts/stop-chemillusion-moodle-dev.sh
scripts/seed-users.php
scripts/install-local-chemillusion.sh
scripts/run-smoke-tests.sh
scripts/reset-moodle-db.sh                 # optional
```

### Moodle mounts

The dev stack should mount:

```text
../moodle-local-chemillusion -> /var/www/html/local/chemillusion
```

### Dev-only LTI consumer plugin

Preferred name to avoid confusion with future production `mod_chemillusion`:

```text
mod/chemillusiondev/version.php
mod/chemillusiondev/lib.php
mod/chemillusiondev/mod_form.php
mod/chemillusiondev/view.php
mod/chemillusiondev/settings.php
mod/chemillusiondev/jwks.php
mod/chemillusiondev/launch.php
mod/chemillusiondev/deeplink.php
mod/chemillusiondev/classes/local/jwt_helper.php
mod/chemillusiondev/classes/local/lti_launch_builder.php
mod/chemillusiondev/lang/en/chemillusiondev.php
```

Purpose:

- emit synthetic/realistic Moodle LTI 1.3 launches into local ChemIllusion
- test OIDC login URL, launch URL, JWKS, deep-linking, session creation
- test future AGS simulation without making gradebook a Phase 1 product feature
- test public-tool/MCP health through ChemIllusion public endpoints

### Seed users

```text
admin@example.com      site admin
teacher@example.com    editing teacher
student@example.com    student
dev@example.com        generic test user
```

### Local URLs

```text
Moodle:             http://localhost:8080
ChemIllusion API:   http://host.docker.internal:8000
ChemIllusion UI:    http://host.docker.internal:5173
```

### Smoke test checklist

```text
1. Install/upgrade local_chemillusion.
2. Configure local-only mode.
3. Run PubChem lookup for caffeine.
4. Create molecule deck.
5. Enable RDKit WASM mode.
6. Render aspirin SVG locally.
7. Detect ester + carboxylic acid groups.
8. Generate highlighted-group flashcard.
9. Enable ChemIllusion account linking.
10. Click student CTA and complete link flow.
11. Click teacher demo CTA.
12. Run chemillusiondev LTI launch.
13. Run deep-linking smoke test.
14. Run public-tool/MCP health check.
```

---

## 11.3 `chem-art-generator` — private ChemIllusion repo

This repo supplies the SaaS funnel and advanced capabilities. Keep it private.

### Backend files to add/update

```text
backend/app/api/public_moodle.py
backend/app/api/lti.py                         # if existing, extend only
backend/app/services/moodle_link_service.py
backend/app/services/moodle_launch_service.py
backend/app/services/public_tool_service.py
backend/app/services/moodle_conversion_service.py
backend/app/services/visual_card_preview_service.py
backend/app/services/rdkit_public_service.py    # wraps existing RDKit tools safely
backend/app/schemas/moodle_public.py
backend/app/schemas/public_tools.py
backend/tests/integration/test_moodle_account_link.py
backend/tests/integration/test_moodle_public_tools.py
backend/tests/integration/test_moodle_lti_launch.py
backend/tests/unit/test_moodle_conversion_service.py
```

### Public endpoints

```http
GET  /api/public/moodle/health
POST /api/public/moodle/link/start
POST /api/public/moodle/link/complete
POST /api/public/moodle/launch
POST /api/public/moodle/visual-card/start
POST /api/public/tools/resolve-molecule
POST /api/public/tools/describe-molecule-basic
POST /api/public/tools/functional-groups
POST /api/public/tools/render-molecule
GET  /api/public/tools/health
```

Phase 1A only needs link/launch/health and possibly a small public tool endpoint. Phase 1B mostly runs local RDKit in-browser, but ChemIllusion endpoints provide premium escalation.

### Frontend files to add/update

```text
frontend/src/pages/moodle/MoodleLinkLandingPage.tsx
frontend/src/pages/moodle/MoodleContinuePage.tsx
frontend/src/pages/moodle/MoodleVisualCardStartPage.tsx
frontend/src/components/moodle/MoodleSourceBanner.tsx
frontend/src/components/moodle/StudentSignupCTA.tsx
frontend/src/components/moodle/TeacherDemoCTA.tsx
frontend/src/services/moodleIntegrationApi.ts
frontend/src/types/moodleIntegration.ts
frontend/src/routes/moodleRoutes.ts            # if routing separated
```

### Existing ChemIllusion systems to reuse carefully

Reuse behind private API boundaries:

```text
ChemicalService / PubChem metadata helpers
RDKit normalization/rendering service
ChemlingoService for account-linked pronunciation/readout
ketcherHighlightBridge concepts only; do not FOSS overlay
external_image_api / image generation budget logic
OpenRouter BYOK routing
account/subscription logic
```

Do not copy these into the Moodle repo.

### Environment flags

```bash
CHEMILLUSION_MOODLE_PUBLIC_API_ENABLED=true
CHEMILLUSION_MOODLE_ACCOUNT_LINK_ENABLED=true
CHEMILLUSION_MOODLE_ALLOWED_ORIGINS=http://localhost:8080,https://*.trustedmoodle.example
CHEMILLUSION_MOODLE_SIGNING_SECRET=...
CHEMILLUSION_MOODLE_DEV_MODE=true
CHEMILLUSION_PUBLIC_TOOL_API_ENABLED=true
CHEMILLUSION_MOODLE_VISUAL_CARD_ENABLED=true
CHEMILLUSION_MCP_HEALTHCHECK_ENABLED=true
```

### Conversion handling

ChemIllusion should record source events only after user/admin consent:

```text
moodle_link_started
moodle_link_completed
moodle_student_signup_started
moodle_teacher_demo_clicked
moodle_visual_card_started
moodle_open_in_generator_clicked
```

---

## 12. UX requirements

### 12.1 Student local study flow

1. Student opens ChemIllusion Study Cards in Moodle.
2. Student searches “aspirin.”
3. Plugin resolves PubChem metadata.
4. Phase 1B renders structure locally using RDKit WASM.
5. Plugin detects ester/carboxylic acid functional groups.
6. Student studies cards.
7. Student sees CTA: “Save this deck to ChemIllusion.”
8. Student links/creates ChemIllusion account.

### 12.2 Teacher deck flow

1. Teacher opens ChemIllusion tools in Moodle.
2. Teacher pastes list of molecules/reagents.
3. Plugin creates course deck.
4. Teacher previews student cards.
5. Teacher publishes deck locally.
6. Teacher sees CTA: “Turn this deck into a guided ChemIllusion activity.”
7. Teacher links account or books demo.

### 12.3 Visual card taste flow

1. Student sees a free RDKit-rendered card.
2. Student clicks “Make this a visual ChemIllusion card.”
3. If not linked, student signs up/logs in.
4. ChemIllusion opens a visual card creation page with molecule state.
5. Image generation occurs on ChemIllusion side under normal user/account limits.

### 12.4 Accessibility UX

Every molecule visual must include:

- text name/identifier
- formula/MW where available
- detected functional groups in text
- screen-reader labels for buttons
- keyboard-operable flashcards
- visible focus states
- non-color-only highlight labels

---

## 13. Technical architecture

### 13.1 Phase 1A architecture

```text
Moodle PHP plugin
  ├─ settings/privacy/capabilities
  ├─ PubChem REST client
  ├─ local cache
  ├─ local study decks/cards
  ├─ account-link launch token
  └─ ChemIllusion CTA links

ChemIllusion SaaS
  ├─ link landing page
  ├─ account signup/login
  ├─ premium tools
  └─ conversion metadata
```

### 13.2 Phase 1B architecture

```text
Moodle PHP plugin
  ├─ serves page/templates
  ├─ serves bundled RDKit.js/WASM static assets
  └─ stores deck/card metadata when user saves

Browser
  ├─ lazy-loads RDKit WASM
  ├─ validates SMILES
  ├─ renders molecule SVG
  ├─ detects functional groups using SMARTS
  └─ generates preview/highlight cards

ChemIllusion SaaS
  └─ optional premium escalation only after user click/link
```

### 13.3 Data flow

Local-only:

```text
Moodle page → PubChem if enabled → Moodle cache → browser study UI
```

RDKit WASM mode:

```text
Moodle page → browser RDKit WASM → local SVG/highlights → optional save to Moodle
```

ChemIllusion linked mode:

```text
Moodle plugin → signed launch token → ChemIllusion account → premium action
```

---

## 14. Security requirements

- Use `require_login()` on user-facing pages.
- Use Moodle capabilities for admin/teacher functions.
- Use `sesskey` for all state-changing actions.
- Use `required_param()` / `optional_param()` and proper Moodle parameter types.
- Escape all output.
- Never trust client-side RDKit results for security-sensitive decisions.
- Treat browser-generated card payloads as untrusted; validate and sanitize before storage.
- Do not expose ChemIllusion signing secrets in JS.
- Do not store API keys in JavaScript.
- Do not store raw OpenRouter keys in Moodle for Phase 1.

---

## 15. Accessibility requirements

- All flashcards keyboard-operable.
- Visible focus states.
- Buttons have clear labels.
- SVG structures have adjacent text descriptions.
- Functional-group highlights have text labels and are not color-only.
- Error messages are associated with inputs.
- Works at 200% zoom and is usable at 400% where feasible.
- Provide “show text-only version” for every card.
- Do not auto-flip cards without user action.

---

## 16. Acceptance criteria

### 16.1 Phase 1A acceptance

- Plugin installs from ZIP into local Moodle dev harness.
- Plugin has GPL license, README, issue tracker, and privacy documentation.
- Admin can select local-only mode.
- Admin can enable/disable PubChem lookup.
- Student/teacher can resolve common molecules.
- Teacher can create a local study deck.
- Student can study deck locally.
- Account-link CTA generates signed ChemIllusion state.
- ChemIllusion link landing page receives source metadata.
- No gradebook integration exists.
- Moodle Plugin CI passes basic checks.

### 16.2 Phase 1B acceptance

- RDKit.js/WASM is bundled in plugin ZIP with license and `thirdpartylibs.xml` entry.
- RDKit WASM loads lazily only on ChemIllusion tool pages.
- Browser can render SVG for at least 20 common Organic 1/2 molecules.
- Browser can validate good/bad SMILES.
- Functional-group detection works for curated group set.
- Highlighted-group flashcards work with accessible labels.
- Plugin degrades gracefully if RDKit WASM fails.
- No external CDN is required for RDKit.
- Release ZIP remains acceptable in size and installs cleanly.

---

## 17. Initial functional-group registry

Phase 1B should start with common Organic 1/2 groups only:

```text
alkene
alkyne
aromatic ring
alkyl halide
alcohol
phenol
ether
epoxide
aldehyde
ketone
carboxylic acid
ester
amide
amine
nitrile
thiol
sulfide
```

Each entry should include:

```text
id
label
SMARTS
student summary
common mistake
organic level
example molecules
```

Do not try to solve every substructure edge case in the first release. Prefer a small validated registry over a large noisy one.

---

## 18. Initial molecule/reagent seed content

### Molecules

```text
methane
ethane
ethene
ethyne
benzene
toluene
phenol
ethanol
diethyl ether
acetone
acetaldehyde
acetic acid
ethyl acetate
acetamide
aniline
aspirin
acetaminophen
caffeine
lidocaine
ibuprofen
```

### Reagents

```text
PCC
DIBAL-H
LAH
NaBH4
mCPBA
NBS
HBr
Br2
OsO4
O3
TsCl
SOCl2
PBr3
```

---

## 19. Release plan

### Milestone 0 — repo bootstrap

- Create `scottmreed/moodle-local-chemillusion`.
- Add GPL license and README.
- Add plugin skeleton.
- Add Moodle Plugin CI.
- Add local dev linking script.

### Milestone 1 — Phase 1A local utility

- Settings page.
- PubChem client.
- Lookup page.
- Deck/card data model.
- Reagent and functional-group dictionaries.
- Account-link CTA.
- Privacy API provider.

### Milestone 2 — Phase 1A funnel integration

- ChemIllusion link/start endpoint.
- ChemIllusion Moodle landing page.
- Student and teacher CTA tracking.
- Static visual examples.
- Local Moodle smoke tests.

### Milestone 3 — Phase 1B RDKit WASM

- Add RDKit.js bundle.
- Add `thirdpartylibs.xml` entry.
- Add lazy loader.
- Add SVG rendering.
- Add SMILES validation.
- Add functional-group SMARTS matching.
- Add highlighted flashcards.

### Milestone 4 — beta package

- Build release ZIP.
- Install in `Moodle-plugin-dev`.
- Run full smoke tests.
- Capture screenshots.
- Update docs.
- Publish GitHub beta release.

---

## 20. Risks and mitigations

| Risk | Mitigation |
|---|---|
| Moodle reviewers dislike bundled WASM | Make RDKit WASM Phase 1B optional, documented, license-clean, lazy-loaded, and not required for baseline plugin. |
| ZIP gets too large | Use minimal RDKit.js build if possible; document size; lazy load. |
| GPL boundary confusion | Keep Moodle repo narrow; document SaaS/API boundary; do not copy private ChemIllusion internals. |
| Existing Moodle chemistry plugins reduce novelty | Position around RDKit-powered study cards, accessibility, account linking, and ChemIllusion visual/AI escalation. |
| Student privacy concerns | Local-only default; no grades; no traces sent without opt-in. |
| RDKit functional-group matching produces false positives | Start with small curated Organic 1/2 registry and test fixtures. |
| Browser compatibility | Graceful fallback to Phase 1A text/PubChem mode. |
| Teacher sees it as just flashcards | Add teacher CTAs to guided activities, image cards, accessibility readout, and demos. |

---

## 21. Source notes

- Moodle plugin code must be GPL-compatible and third-party bundled libraries must be GPLv3-compatible for Moodle Plugins Directory packaging.
- Moodle plugin ZIP install is the target user/admin path; admin-run Composer or shell access should not be required.
- RDKit.js is the official JavaScript/WASM distribution of RDKit functionality and is BSD-3-Clause licensed.
- Existing Moodle chemistry plugins already cover chemical drawing and some drawn-answer workflows, so ChemIllusion should position around study tooling, RDKit-powered browser chemistry, accessibility, account linking, and SaaS escalation rather than claiming first drawing support.
- Existing ChemIllusion docs already define `local_chemillusion` as the Phase 1 plugin target, minimal Moodle data storage, local Moodle dev testing, and a private SaaS boundary for the full Ketcher overlay, full harness, MCP inventory, image/video tools, and billing.
