# Moodle Directory Submission Readiness Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Prepare `local_chemillusion` for the Moodle Plugins Directory public submission by completing Privacy API coverage, fixing naming consistency, hardening packaging, fixing the alt_types return schema, standardizing bootstrap paths, and updating all supporting documentation.

**Architecture:** All changes are in-place edits to existing files — no new files created except new privacy strings and test expansions. The Privacy API provider is the central file for tasks 3–4; 10 top-level page scripts share the same one-line bootstrap fix; docs are updated last after code is solid.

**Tech Stack:** PHP 8.1+, Moodle 4.3+, Moodle Privacy API, Moodle external_api, PHPUnit (Moodle flavour), Bash, Moodle lang string system.

## Global Constraints

- `$plugin->component = 'local_chemillusion'` — never rename
- User-facing display name must be **ChemIllusion Study Tools** (not "Study Cards", not "Chemistry Study Tools")
- DB tables, capabilities, AMD module names, CSS classes, PHP namespaces — keep `local_chemillusion` prefix unchanged
- All PHP files must pass `php -l`
- `$plugin->requires = 2023100900` (Moodle 4.3.0), `$plugin->supported = [403, 500]`
- Supported range: 4.3 through 5.0.x; automated CI: 4.3 and 4.5; local dev/testing: 5.0.8
- No Composer, shell, Conda, or native packages required to install from ZIP

---

## File Map

| File | Task | Change |
|------|------|--------|
| `lang/en/local_chemillusion.php` | T2, T3, T4 | Update pluginname/dashboard_heading; add events + PubChem privacy strings |
| `classes/privacy/provider.php` | T3, T4 | Add events metadata, context discovery, user listing, export, delete; add PubChem external location |
| `tests/phpunit/privacy_provider_test.php` | T3 | Expand tests for events coverage and PubChem metadata |
| `classes/external/lookup_molecule.php` | T5 | Change alt_types from scalar to external_multiple_structure |
| `index.php` | T6 | Replace DOCUMENT_ROOT bootstrap |
| `link.php` | T6 | Replace DOCUMENT_ROOT bootstrap |
| `privacy.php` | T6 | Replace DOCUMENT_ROOT bootstrap |
| `cards.php` | T6 | Replace DOCUMENT_ROOT bootstrap |
| `tools.php` | T6 | Replace DOCUMENT_ROOT bootstrap |
| `graphical.php` | T6 | Replace DOCUMENT_ROOT bootstrap |
| `card_view.php` | T6 | Replace DOCUMENT_ROOT bootstrap |
| `card_edit.php` | T6 | Replace DOCUMENT_ROOT bootstrap |
| `launch.php` | T6 | Replace DOCUMENT_ROOT bootstrap |
| `card_export.php` | T6 | Replace DOCUMENT_ROOT bootstrap |
| `scripts/package-plugin.sh` | T7 | Fail hard when RDKit expected but missing; add --without-rdkit flag |
| `README.md` | T2, T8 | Rename plugin, add SaaS/subscription disclosure, update Moodle version range |
| `version.php` | T2 | Fix docblock comment "Study Cards" → "Study Tools" |
| `CONTRIBUTING.md` | T2 | Rename plugin in intro |
| `docs/moodle-directory-submission.md` | T2, T8 | Rename, update version range/CI table, update checklist |
| `docs/privacy-and-data-flow.md` | T4, T8 | Add PubChem privacy section, add SaaS disclosure |
| `docs/local-dev-testing.md` | T8 | Note 5.0.8 local dev |
| `docs/release-checklist.md` | T7, T8 | Add RDKit hard-fail note, update Moodle version range |
| `CHANGELOG.md` | T8 | Add [Unreleased] entries for these changes |
| `.gitignore` | T1 | Add *.env, .env* patterns |

---

## Task 1: Gitignore public-readiness check

**Files:**
- Modify: `.gitignore`

**Interfaces:**
- Consumes: nothing
- Produces: `.gitignore` with env-file patterns

- [ ] **Step 1: Verify no secrets are tracked**

```bash
grep -R "signing_secret\|api_key\|password\|secret" \
  /Users/scottreed/PycharmProjects/moodle-local-chemillusion \
  --include="*.php" --include="*.env" --include="*.json" \
  -n --exclude-dir=.git --exclude-dir=vendor \
  | grep -v "settings_signing_secret\|privacy\|lang\|test\|comment\|docblock\|// \|#"
```

Expected: no hardcoded secrets (signing_secret in lang/settings.php is a settings key label, not a value — that's fine).

- [ ] **Step 2: Add env-file patterns to .gitignore**

Current `.gitignore` content:
```
# Build / packaging artefacts
/dist/
*.zip

# Node tooling (dev only; build outputs in amd/build are committed)
/node_modules/
npm-debug.log*
package-lock.json

# Editor / OS
.DS_Store
.idea/
*.swp
*.log

testing/
```

Replace with:
```
# Build / packaging artefacts
/dist/
*.zip

# Node tooling (dev only; build outputs in amd/build are committed)
/node_modules/
npm-debug.log*
package-lock.json

# Secrets / environment files — never commit these
.env
.env.*
*.env
*.local

# Editor / OS
.DS_Store
.idea/
*.swp
*.log

# Local development setup (not part of the plugin)
testing/
```

- [ ] **Step 3: PHP lint all PHP files to confirm no syntax errors before any changes**

```bash
find /Users/scottreed/PycharmProjects/moodle-local-chemillusion \
  -name '*.php' \
  -not -path '*/vendor/*' \
  -not -path '*/dist/*' \
  -print0 | xargs -0 -n1 php -l 2>&1 | grep -v "No syntax errors"
```

Expected: empty output (no errors).

- [ ] **Step 4: Commit**

```bash
cd /Users/scottreed/PycharmProjects/moodle-local-chemillusion
git add .gitignore
git commit -m "chore: harden .gitignore for public release"
```

---

## Task 2: Rename plugin display name to "ChemIllusion Study Tools"

**Files:**
- Modify: `lang/en/local_chemillusion.php:27-32`
- Modify: `README.md:1,36`
- Modify: `version.php:18`
- Modify: `CONTRIBUTING.md:3`

**Interfaces:**
- Consumes: nothing
- Produces: consistent "ChemIllusion Study Tools" name in all user-facing and dev-facing text

**Note on current state:** `pluginname` is already "ChemIllusion Chemistry Study Tools" — spec wants "ChemIllusion Study Tools" (drop "Chemistry"). `dashboard_heading` and `settings_info_heading` need the same change. Three other files say "Study Cards" (README title, version.php docblock, CONTRIBUTING.md intro).

- [ ] **Step 1: Update lang string names and headings**

In `lang/en/local_chemillusion.php`, make these changes:

Line 27 — change:
```php
$string['pluginname'] = 'ChemIllusion Chemistry Study Tools';
```
to:
```php
$string['pluginname'] = 'ChemIllusion Study Tools';
```

Line 28 — change:
```php
$string['plugindescription'] = 'Privacy-aware chemistry study tools for Moodle: molecule lookup, RDKit.js structure rendering, functional-group highlighting, study flashcards, graphical cards, Newman projections, orbital diagrams, reaction coordinate charts, and optional ChemIllusion account linking. More tools and premium features available at <a href="https://chemillusion.com">chemillusion.com</a>. Support: <a href="mailto:support@chemillusion.com">support@chemillusion.com</a>.';
```
to:
```php
$string['plugindescription'] = 'Privacy-aware chemistry study tools for Moodle: molecule lookup, RDKit.js structure rendering, functional-group highlighting, study flashcards, and optional ChemIllusion account linking. More tools and premium features available at <a href="https://chemillusion.com">chemillusion.com</a>. Support: <a href="mailto:support@chemillusion.com">support@chemillusion.com</a>.';
```

Line 32 — change:
```php
$string['dashboard_heading'] = 'ChemIllusion Chemistry Study Tools';
```
to:
```php
$string['dashboard_heading'] = 'ChemIllusion Study Tools';
```

Line 40 — change:
```php
$string['settings_info_heading'] = 'ChemIllusion Chemistry Study Tools';
```
to:
```php
$string['settings_info_heading'] = 'ChemIllusion Study Tools';
```

- [ ] **Step 2: Update README.md title and install step**

In `README.md` line 1, change:
```markdown
# ChemIllusion Study Cards for Moodle (`local_chemillusion`)
```
to:
```markdown
# ChemIllusion Study Tools for Moodle (`local_chemillusion`)
```

In `README.md` line 36, change:
```markdown
3. Complete the upgrade, then visit **Site administration → Plugins → Local plugins → ChemIllusion Study Cards** to configure mode, external services, and privacy.
```
to:
```markdown
3. Complete the upgrade, then visit **Site administration → Plugins → Local plugins → ChemIllusion Study Tools** to configure mode, external services, and privacy.
```

- [ ] **Step 3: Update version.php docblock**

In `version.php` line 18, change:
```php
 * Version metadata for the ChemIllusion Study Cards local plugin.
```
to:
```php
 * Version metadata for the ChemIllusion Study Tools local plugin.
```

- [ ] **Step 4: Update CONTRIBUTING.md intro**

In `CONTRIBUTING.md` line 3, change:
```markdown
Thanks for your interest in improving ChemIllusion Study Cards for Moodle. This
```
to:
```markdown
Thanks for your interest in improving ChemIllusion Study Tools for Moodle. This
```

- [ ] **Step 5: Verify no old name remains in code/docs**

```bash
grep -R "ChemIllusion Study Cards\|Chemistry Study Tools" \
  /Users/scottreed/PycharmProjects/moodle-local-chemillusion \
  --include="*.php" --include="*.md" --include="*.mustache" \
  -n --exclude-dir=.git --exclude-dir=dist \
  | grep -v "chemillusion_moodle_phase_1a_1b_prd.md"
```

Expected: empty output (the PRD file is an internal doc — leaving it is fine).

- [ ] **Step 6: Commit**

```bash
cd /Users/scottreed/PycharmProjects/moodle-local-chemillusion
git add lang/en/local_chemillusion.php README.md version.php CONTRIBUTING.md
git commit -m "chore: rename plugin display name to ChemIllusion Study Tools"
```

---

## Task 3: Complete Privacy API coverage for events and PubChem

**Files:**
- Modify: `classes/privacy/provider.php`
- Modify: `lang/en/local_chemillusion.php` (append privacy strings)
- Modify: `tests/phpunit/privacy_provider_test.php`

**Interfaces:**
- Consumes: `$DB->get_records('local_chemillusion_events', ...)` — existing table; schema has `userid`, `courseid`, `eventname`, `surface`, `cta`, `payload`, `created_at`
- Produces: complete Privacy API (metadata + discovery + listing + export + delete) for events and PubChem external location; expanded PHPUnit tests

### 3a: Add privacy lang strings

- [ ] **Step 1: Append events and PubChem privacy strings to lang file**

At the end of `lang/en/local_chemillusion.php`, after the last `$string['privacy:...']` line (currently line 310), add:

```php
$string['privacy:metadata:local_chemillusion_events'] = 'Coarse local usage events for ChemIllusion Study Tools.';
$string['privacy:metadata:local_chemillusion_events:userid'] = 'The Moodle user associated with the event when minimal mode is disabled.';
$string['privacy:metadata:local_chemillusion_events:courseid'] = 'The Moodle course associated with the event, if any.';
$string['privacy:metadata:local_chemillusion_events:eventname'] = 'The coarse event type, such as molecule lookup or deck creation.';
$string['privacy:metadata:local_chemillusion_events:surface'] = 'The plugin surface where the event occurred.';
$string['privacy:metadata:local_chemillusion_events:cta'] = 'The call-to-action identifier, if applicable.';
$string['privacy:metadata:local_chemillusion_events:payload'] = 'Reserved event payload field. The current implementation stores null.';
$string['privacy:metadata:local_chemillusion_events:created_at'] = 'When the event was recorded.';
$string['privacy:metadata:pubchem_pug_rest'] = 'PubChem PUG REST may receive the molecule identifier entered by the user when PubChem lookup is enabled.';
$string['privacy:metadata:pubchem_pug_rest:identifier'] = 'The molecule name, SMILES, InChI, or InChIKey submitted for lookup.';
```

### 3b: Update the privacy provider

- [ ] **Step 2: Add events table metadata in get_metadata()**

In `classes/privacy/provider.php`, after the `add_external_location_link('chemillusion_saas', ...)` block (lines 71–75) and before `return $collection;` (line 77), add:

```php
        $collection->add_database_table('local_chemillusion_events', [
            'userid'     => 'privacy:metadata:local_chemillusion_events:userid',
            'courseid'   => 'privacy:metadata:local_chemillusion_events:courseid',
            'eventname'  => 'privacy:metadata:local_chemillusion_events:eventname',
            'surface'    => 'privacy:metadata:local_chemillusion_events:surface',
            'cta'        => 'privacy:metadata:local_chemillusion_events:cta',
            'payload'    => 'privacy:metadata:local_chemillusion_events:payload',
            'created_at' => 'privacy:metadata:local_chemillusion_events:created_at',
        ], 'privacy:metadata:local_chemillusion_events');

        $collection->add_external_location_link('pubchem_pug_rest', [
            'identifier' => 'privacy:metadata:pubchem_pug_rest:identifier',
        ], 'privacy:metadata:pubchem_pug_rest');
```

- [ ] **Step 3: Add events to get_contexts_for_userid()**

In `classes/privacy/provider.php`, replace lines 89–90:
```php
        $has = $DB->record_exists('local_chemillusion_links', ['userid' => $userid])
            || $DB->record_exists('local_chemillusion_decks', ['userid' => $userid]);
```
with:
```php
        $has = $DB->record_exists('local_chemillusion_links', ['userid' => $userid])
            || $DB->record_exists('local_chemillusion_decks', ['userid' => $userid])
            || $DB->record_exists('local_chemillusion_events', ['userid' => $userid]);
```

- [ ] **Step 4: Add events to get_users_in_context()**

In `classes/privacy/provider.php`, after line 109:
```php
        $userlist->add_from_sql('userid', 'SELECT userid FROM {local_chemillusion_decks}', []);
```
add:
```php
        $userlist->add_from_sql('userid',
            'SELECT userid FROM {local_chemillusion_events} WHERE userid IS NOT NULL', []);
```

- [ ] **Step 5: Add events export in export_user_data()**

In `classes/privacy/provider.php`, after the deck export block (after line 136 which closes the foreach on decks), add the events export. Insert after line 137 (the closing of the deck foreach, before the closing `}` of the system context check):

```php
            $events = $DB->get_records('local_chemillusion_events', ['userid' => $userid], 'created_at ASC');
            if ($events) {
                writer::with_context($context)->export_data(
                    [get_string('pluginname', 'local_chemillusion'), 'events'],
                    (object) ['events' => array_values($events)]
                );
            }
```

- [ ] **Step 6: Add events deletion in delete_data_for_all_users_in_context()**

In `classes/privacy/provider.php`, in `delete_data_for_all_users_in_context()`, after line 157:
```php
        $DB->delete_records('local_chemillusion_links', []);
```
add:
```php
        $DB->delete_records('local_chemillusion_events', []);
```

- [ ] **Step 7: Add events deletion in delete_for_userids() helper**

In `classes/privacy/provider.php`, in `delete_for_userids()`, after line 207:
```php
        $DB->delete_records_select('local_chemillusion_links', "userid $insql", $params);
```
add:
```php
        $DB->delete_records_select('local_chemillusion_events', "userid $insql", $params);
```

### 3c: Expand privacy tests

- [ ] **Step 8: Write failing tests first — add to privacy_provider_test.php**

Replace the full content of `tests/phpunit/privacy_provider_test.php` with:

```php
<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

namespace local_chemillusion\phpunit;

use local_chemillusion\privacy\provider;
use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\writer;

/**
 * Unit tests for the privacy provider.
 *
 * @package    local_chemillusion
 * @copyright  2026 MolLogic / Scott Reed
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_chemillusion\privacy\provider
 */
final class privacy_provider_test extends \advanced_testcase {

    public function test_get_metadata_is_populated(): void {
        $this->resetAfterTest();
        $collection = provider::get_metadata(new collection('local_chemillusion'));
        $this->assertNotEmpty($collection->get_collection());
    }

    public function test_metadata_includes_events_table(): void {
        $this->resetAfterTest();
        $collection = provider::get_metadata(new collection('local_chemillusion'));
        $items = $collection->get_collection();
        $names = array_map(fn($item) => $item->get_name(), $items);
        $this->assertContains('local_chemillusion_events', $names,
            'Privacy metadata must declare local_chemillusion_events');
    }

    public function test_metadata_includes_pubchem_external_location(): void {
        $this->resetAfterTest();
        $collection = provider::get_metadata(new collection('local_chemillusion'));
        $items = $collection->get_collection();
        $names = array_map(fn($item) => $item->get_name(), $items);
        $this->assertContains('pubchem_pug_rest', $names,
            'Privacy metadata must declare pubchem_pug_rest external location');
    }

    public function test_events_create_context_for_userid(): void {
        global $DB;
        $this->resetAfterTest();
        $user = $this->getDataGenerator()->create_user();
        $system = \context_system::instance();

        $DB->insert_record('local_chemillusion_events', (object) [
            'userid'    => $user->id,
            'courseid'  => 0,
            'eventname' => 'molecule_lookup',
            'surface'   => 'tools',
            'cta'       => null,
            'payload'   => null,
            'created_at' => time(),
        ]);

        $contexts = provider::get_contexts_for_userid($user->id);
        $this->assertContains($system->id, $contexts->get_contextids(),
            'An event row with a userid must create a system context entry');
    }

    public function test_contexts_export_and_delete(): void {
        global $DB;
        $this->resetAfterTest();
        $user = $this->getDataGenerator()->create_user();
        $system = \context_system::instance();

        $DB->insert_record('local_chemillusion_links',
            (object) ['userid' => $user->id, 'linked_at' => time(), 'status' => 'linked']);
        $deckid = $DB->insert_record('local_chemillusion_decks', (object) [
            'userid' => $user->id, 'name' => 'D', 'visibility' => 'private',
            'source' => 'manual', 'created_at' => time(), 'updated_at' => time(),
        ]);
        $DB->insert_record('local_chemillusion_cards', (object) [
            'deckid' => $deckid, 'cardtype' => 'molecule_identity', 'prompt' => 'p',
            'answer' => 'a', 'sortorder' => 0, 'created_at' => time(),
        ]);
        $DB->insert_record('local_chemillusion_events', (object) [
            'userid'    => $user->id,
            'courseid'  => 0,
            'eventname' => 'molecule_lookup',
            'surface'   => 'tools',
            'cta'       => null,
            'payload'   => null,
            'created_at' => time(),
        ]);

        $contexts = provider::get_contexts_for_userid($user->id);
        $this->assertContains($system->id, $contexts->get_contextids());

        $approved = new approved_contextlist($user, 'local_chemillusion', [$system->id]);
        provider::export_user_data($approved);
        $this->assertTrue(writer::with_context($system)->has_any_data());

        provider::delete_data_for_user($approved);
        $this->assertFalse($DB->record_exists('local_chemillusion_links', ['userid' => $user->id]));
        $this->assertFalse($DB->record_exists('local_chemillusion_decks', ['userid' => $user->id]));
        $this->assertFalse($DB->record_exists('local_chemillusion_cards', ['deckid' => $deckid]));
        $this->assertFalse($DB->record_exists('local_chemillusion_events', ['userid' => $user->id]),
            'Deleting user data must remove their event rows');
    }

    public function test_delete_user_does_not_affect_other_users_events(): void {
        global $DB;
        $this->resetAfterTest();
        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();
        $system = \context_system::instance();

        foreach ([$user1->id, $user2->id] as $uid) {
            $DB->insert_record('local_chemillusion_events', (object) [
                'userid'    => $uid,
                'courseid'  => 0,
                'eventname' => 'molecule_lookup',
                'surface'   => 'tools',
                'cta'       => null,
                'payload'   => null,
                'created_at' => time(),
            ]);
        }

        $approved = new approved_contextlist($user1, 'local_chemillusion', [$system->id]);
        provider::delete_data_for_user($approved);

        $this->assertFalse($DB->record_exists('local_chemillusion_events', ['userid' => $user1->id]));
        $this->assertTrue($DB->record_exists('local_chemillusion_events', ['userid' => $user2->id]),
            'Deleting user1 must not remove user2 event rows');
    }

    public function test_delete_all_users_clears_events(): void {
        global $DB;
        $this->resetAfterTest();
        $user = $this->getDataGenerator()->create_user();
        $system = \context_system::instance();

        $DB->insert_record('local_chemillusion_events', (object) [
            'userid'    => $user->id,
            'courseid'  => 0,
            'eventname' => 'deck_created',
            'surface'   => 'cards',
            'cta'       => null,
            'payload'   => null,
            'created_at' => time(),
        ]);

        provider::delete_data_for_all_users_in_context($system);
        $this->assertEquals(0, $DB->count_records('local_chemillusion_events'),
            'delete_data_for_all_users_in_context must clear event rows');
    }
}
```

- [ ] **Step 9: PHP lint both changed files**

```bash
php -l /Users/scottreed/PycharmProjects/moodle-local-chemillusion/classes/privacy/provider.php
php -l /Users/scottreed/PycharmProjects/moodle-local-chemillusion/lang/en/local_chemillusion.php
php -l /Users/scottreed/PycharmProjects/moodle-local-chemillusion/tests/phpunit/privacy_provider_test.php
```

Expected: `No syntax errors detected` for each.

- [ ] **Step 10: Commit**

```bash
cd /Users/scottreed/PycharmProjects/moodle-local-chemillusion
git add classes/privacy/provider.php lang/en/local_chemillusion.php tests/phpunit/privacy_provider_test.php
git commit -m "fix: complete privacy provider coverage for events and PubChem"
```

---

## Task 4: Update privacy-and-data-flow.md to match Privacy API

**Files:**
- Modify: `docs/privacy-and-data-flow.md`

**Interfaces:**
- Consumes: Privacy API additions from Task 3
- Produces: docs that match the Privacy API

- [ ] **Step 1: Replace the Privacy API section and add PubChem + SaaS disclosure**

Replace the entire content of `docs/privacy-and-data-flow.md` with:

```markdown
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
```

- [ ] **Step 2: Commit**

```bash
cd /Users/scottreed/PycharmProjects/moodle-local-chemillusion
git add docs/privacy-and-data-flow.md
git commit -m "docs: update privacy-and-data-flow.md to match Privacy API"
```

---

## Task 5: Fix alt_types external return schema

**Files:**
- Modify: `classes/external/lookup_molecule.php:119`

**Interfaces:**
- Consumes: existing `execute()` which sets `$response['alt_types'] = $result['alt_types']` where `alt_types` is an array
- Produces: `execute_returns()` that correctly declares alt_types as `external_multiple_structure`

- [ ] **Step 1: Add the external_multiple_structure import**

In `classes/external/lookup_molecule.php`, after the existing use statements (after line 24), add:

```php
use core_external\external_multiple_structure;
```

- [ ] **Step 2: Replace the alt_types return declaration**

In `classes/external/lookup_molecule.php`, in `execute_returns()`, replace line 119:
```php
            'alt_types'        => new external_value(PARAM_ALPHA, 'Comma-separated alternative types to suggest retrying', VALUE_OPTIONAL),
```
with:
```php
            'alt_types'        => new external_multiple_structure(
                new external_value(PARAM_ALPHA, 'Alternative input type'),
                'Alternative types to suggest retrying',
                VALUE_OPTIONAL
            ),
```

- [ ] **Step 3: PHP lint**

```bash
php -l /Users/scottreed/PycharmProjects/moodle-local-chemillusion/classes/external/lookup_molecule.php
```

Expected: `No syntax errors detected`.

- [ ] **Step 4: Commit**

```bash
cd /Users/scottreed/PycharmProjects/moodle-local-chemillusion
git add classes/external/lookup_molecule.php
git commit -m "fix: correct lookup_molecule alt_types external return schema to array"
```

---

## Task 6: Fix fragile Moodle bootstrap paths

**Files:**
- Modify: `index.php:26-27`, `link.php:26-27`, `privacy.php:26-27`, `cards.php:26-27`, `tools.php:26-27`, `graphical.php:25-26`, `card_view.php:25-26`, `card_edit.php:25-26`, `launch.php:30-31`, `card_export.php:28-29`

**Interfaces:**
- Consumes: Moodle's standard plugin-relative layout where plugin lives at `moodle/local/chemillusion/`
- Produces: all top-level page scripts use `require_once(__DIR__ . '/../../config.php');`

**Note:** All 10 files have identical pattern to replace. Each has a comment `// Load Moodle config - works with symlinks` above the two lines.

- [ ] **Step 1: Replace bootstrap in all 10 files**

For each of the 10 files below, replace the two-line pattern:
```php
$moodleroot = $_SERVER['DOCUMENT_ROOT'] ?? dirname(__DIR__, 2);
require($moodleroot . '/config.php');
```
with:
```php
require_once(__DIR__ . '/../../config.php');
```

Also remove the comment line `// Load Moodle config - works with symlinks` that precedes it in each file.

Files to update:
1. `index.php` (lines 25–27)
2. `link.php` (lines 25–27)
3. `privacy.php` (lines 25–27)
4. `cards.php` (lines 25–27)
5. `tools.php` (lines 25–27)
6. `graphical.php` (lines 24–26)
7. `card_view.php` (lines 24–26)
8. `card_edit.php` (lines 24–26)
9. `launch.php` (lines 29–31)
10. `card_export.php` (lines 27–29)

- [ ] **Step 2: Verify no DOCUMENT_ROOT remains**

```bash
grep -R "DOCUMENT_ROOT\|moodleroot" \
  /Users/scottreed/PycharmProjects/moodle-local-chemillusion \
  --include="*.php" -n --exclude-dir=.git
```

Expected: empty output.

- [ ] **Step 3: PHP lint all 10 files**

```bash
for f in index.php link.php privacy.php cards.php tools.php graphical.php card_view.php card_edit.php launch.php card_export.php; do
  php -l /Users/scottreed/PycharmProjects/moodle-local-chemillusion/$f
done
```

Expected: `No syntax errors detected` for each.

- [ ] **Step 4: Commit**

```bash
cd /Users/scottreed/PycharmProjects/moodle-local-chemillusion
git add index.php link.php privacy.php cards.php tools.php graphical.php card_view.php card_edit.php launch.php card_export.php
git commit -m "fix: use plugin-relative Moodle config bootstrap in all page scripts"
```

---

## Task 7: Harden RDKit release packaging

**Files:**
- Modify: `scripts/package-plugin.sh`

**Interfaces:**
- Consumes: `thirdparty/rdkit-js/RDKit_minimal.js`, `thirdparty/rdkit-js/RDKit_minimal.wasm`, `thirdparty/rdkit-js/LICENSE`, `thirdparty/rdkit-js/VERSION.txt`
- Produces: script that exits with code 1 when RDKit assets missing and `--without-rdkit` not passed

- [ ] **Step 1: Replace package-plugin.sh**

Replace the full content of `scripts/package-plugin.sh` with:

```bash
#!/usr/bin/env bash
#
# Build a Moodle-installable release ZIP.
#
# Usage:
#   scripts/package-plugin.sh --version 0.1.0 [--moodle moodle45] [--without-rdkit]
#
# Produces: dist/local_chemillusion-<version>-<moodle>.zip
# The ZIP contains a single root folder "chemillusion" (so it installs to
# local/chemillusion).
#
# By default, packaging expects RDKit assets to be present. Run
# scripts/update-rdkit-js.sh first if they are missing.
# Pass --without-rdkit for a local-only test package (RDKit will be absent
# from the ZIP and enable_rdkit should default to 0 in your settings.php).
#
set -euo pipefail

here="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
version=""
moodle="moodle45"
include_rdkit=1

while [ $# -gt 0 ]; do
  case "$1" in
    --version)      version="$2";  shift 2;;
    --moodle)       moodle="$2";   shift 2;;
    --without-rdkit) include_rdkit=0; shift 1;;
    *) echo "Unknown arg: $1" >&2; exit 1;;
  esac
done

if [ -z "${version}" ]; then
  echo "ERROR: --version is required" >&2
  exit 1
fi

if [ "${include_rdkit}" -eq 1 ]; then
  missing=0
  for asset in RDKit_minimal.js RDKit_minimal.wasm LICENSE VERSION.txt; do
    if [ ! -f "${here}/thirdparty/rdkit-js/${asset}" ]; then
      echo "ERROR: missing thirdparty/rdkit-js/${asset}" >&2
      missing=1
    fi
  done
  if [ "${missing}" -eq 1 ]; then
    echo "" >&2
    echo "RDKit Phase 1B assets are missing." >&2
    echo "Run scripts/update-rdkit-js.sh before packaging, or pass --without-rdkit" >&2
    echo "for a local-only package (RDKit rendering will be unavailable)." >&2
    exit 1
  fi
else
  echo "WARNING: Building without RDKit assets. RDKit rendering will be unavailable in this ZIP." >&2
fi

dist="${here}/dist"
stage="$(mktemp -d)"
root="${stage}/chemillusion"
mkdir -p "${root}" "${dist}"

# Copy plugin payload, excluding dev-only and VCS artefacts.
rsync -a \
  --exclude '.git' \
  --exclude 'dist' \
  --exclude 'node_modules' \
  --exclude '.github' \
  --exclude 'scripts' \
  --exclude '*.zip' \
  "${here}/" "${root}/"

# Build JS into amd/build if needed.
if [ ! -d "${root}/amd/build" ] || [ -z "$(ls -A "${root}/amd/build" 2>/dev/null)" ]; then
  echo "amd/build missing; running build-js.sh ..."
  ( cd "${here}" && bash scripts/build-js.sh )
  rsync -a "${here}/amd/build/" "${root}/amd/build/"
fi

# Strip RDKit binaries if --without-rdkit was passed.
if [ "${include_rdkit}" -eq 0 ]; then
  rm -f "${root}/thirdparty/rdkit-js/RDKit_minimal.js"
  rm -f "${root}/thirdparty/rdkit-js/RDKit_minimal.wasm"
fi

out="${dist}/local_chemillusion-${version}-${moodle}.zip"
rm -f "${out}"
( cd "${stage}" && zip -r -q "${out}" chemillusion )
rm -rf "${stage}"

echo "Built ${out}"
```

- [ ] **Step 2: Verify the hard-fail works**

```bash
# Remove one asset to simulate missing RDKit.
mv /Users/scottreed/PycharmProjects/moodle-local-chemillusion/thirdparty/rdkit-js/VERSION.txt \
   /private/tmp/VERSION.txt.bak 2>/dev/null || true

# This must fail with non-zero exit and print the error.
cd /Users/scottreed/PycharmProjects/moodle-local-chemillusion
bash scripts/package-plugin.sh --version 0.1.0 2>&1 || echo "Exit $? — EXPECTED FAILURE"

# Restore.
mv /private/tmp/VERSION.txt.bak \
   /Users/scottreed/PycharmProjects/moodle-local-chemillusion/thirdparty/rdkit-js/VERSION.txt 2>/dev/null || true
```

Expected: script prints the RDKit error message and "Exit 1 — EXPECTED FAILURE".

- [ ] **Step 3: Commit**

```bash
cd /Users/scottreed/PycharmProjects/moodle-local-chemillusion
git add scripts/package-plugin.sh
git commit -m "fix: harden RDKit release packaging — fail hard when assets missing"
```

---

## Task 8: Update documentation (SaaS disclosure + version range + submission checklist)

**Files:**
- Modify: `README.md`
- Modify: `docs/moodle-directory-submission.md`
- Modify: `docs/local-dev-testing.md`
- Modify: `docs/release-checklist.md`
- Modify: `CHANGELOG.md`

**Interfaces:**
- Consumes: all code changes from Tasks 1–7
- Produces: consistent documentation across all files

- [ ] **Step 1: Update README.md — add SaaS disclosure and Moodle version range**

After the `## Privacy at a glance` section (after line 45), add a new section:

```markdown
## ChemIllusion subscription

**ChemIllusion Study Tools works locally without a ChemIllusion subscription.**
Optional account linking / SaaS escalation requires a ChemIllusion account and
may require a paid ChemIllusion plan. Review/demo credentials are available on
request.
```

Also update the install step 3 line (already done in Task 2 to say "Study Tools").

Add/replace the Moodle version support note. In `README.md`, find the line:
```markdown
- **Status:** Alpha (0.1.0) — Phase 1A + Phase 1B scaffolding
```
and change to:
```markdown
- **Status:** Alpha (0.2.0) — Phase 1A + Phase 1B scaffolding
- **Moodle support:** 4.3 through 5.0.x; automated CI on 4.3 and 4.5; local dev/testing on 5.0.8
```

- [ ] **Step 2: Update docs/moodle-directory-submission.md**

Replace the entire content of `docs/moodle-directory-submission.md` with:

```markdown
# Moodle Plugins Directory submission notes

These notes prepare `local_chemillusion` for the Moodle Plugins Directory.
They are based on Moodle's plugin contribution, checklist, code precheck,
third-party library, and plugin documentation guidance.

## Positioning

Submit as a **chemistry study** plugin, not "the first chemical drawing tool".
Lead with: RDKit-powered browser-side structure rendering, accessibility,
functional-group recognition, account linking, and optional ChemIllusion
visual/AI escalation.

## No subscription required

**ChemIllusion Study Tools works locally without a ChemIllusion subscription.**
Optional account linking / SaaS escalation requires a ChemIllusion account and
may require a paid ChemIllusion plan. Review/demo credentials are available on
request.

## Requirements checklist

- [x] `local_chemillusion` frankenstyle component remains unchanged
- [x] User-facing name is **ChemIllusion Study Tools**
- [x] GPL-3.0-or-later license (`LICENSE`)
- [x] `README.md`, `CHANGELOG.md`, `CONTRIBUTING.md`, `SECURITY.md`
- [x] Repository root is the plugin root (`version.php`, `classes/`, `db/`,
  `lang/`, and `templates/` are not nested under an extra directory)
- [x] Repository name follows Moodle convention: `moodle-local_chemillusion`
- [x] `version.php` with component, version, requires, supported, maturity, release
- [x] Language strings in `lang/en/local_chemillusion.php`
- [x] Privacy API covers links, decks, cards, events, ChemIllusion external
  location, and PubChem external location
- [x] External calls are optional and admin-controllable
- [x] Master external-call kill switch exists (`disable_external`)
- [x] Local use works without ChemIllusion subscription
- [x] Optional account linking / SaaS escalation disclosure is present
- [x] RDKit third-party library is declared in `thirdpartylibs.xml`
- [x] Packaging fails hard if RDKit is expected but missing
- [x] Release ZIP installs from Moodle plugin installer without Composer,
  shell, Python, Conda, or native packages
- [x] Capabilities defined in `db/access.php` with matching strings
- [x] Tests: PHPUnit scaffolding; Moodle Plugin CI workflow
- [x] Supported range: 4.3 through 5.0.x
- [x] Automated CI: 4.3 and 4.5
- [x] Local dev/testing: 5.0.8
- [ ] Public issue tracker enabled on GitHub before submission
- [ ] Public repository visibility before submission
- [ ] Screenshots captured for Moodle Plugins Directory listing
- [ ] Release ZIP smoke-tested with developer debugging enabled

## Reviewer-sensitive points (and our mitigations)

- **Bundled WASM**: Phase 1B is optional, documented, license-clean,
  lazy-loaded, and not required for the baseline plugin. See
  `docs/rdkit-wasm-packaging.md`.
- **ZIP size**: minimal RDKit build + lazy loading; size documented.
- **External calls**: all optional and admin-controllable; a master kill-switch
  (`disable_external`) disables every outbound request. See
  `docs/privacy-and-data-flow.md`.
- **Parallel ChemIllusion server**: the plugin is not hard-wired to a private
  server. The ChemIllusion base URL is an admin setting, account linking is
  off by default, and local study tools work without a ChemIllusion
  subscription.
- **GPL boundary**: no private ChemIllusion internals are included. See
  `docs/license-boundary.md`.
- **Privacy**: the plugin implements Moodle's Privacy API covering links,
  decks, cards, events, and both external locations (ChemIllusion SaaS and
  PubChem). Any new external integration must update both the provider and
  this document.
- **Namespace/collision safety**: database tables, capabilities, classes,
  strings, AMD modules, templates, and settings use the `local_chemillusion`
  frankenstyle prefix.

## Supported Moodle versions

Supported range: Moodle 4.3 through Moodle 5.0.x.
Automated CI: Moodle 4.3 and Moodle 4.5.
Local development/testing: Moodle 5.0.8.

In `version.php`:
```php
$plugin->requires  = 2023100900; // Moodle 4.3.0 and later.
$plugin->supported = [403, 500]; // Moodle 4.3 through 5.0.x.
```

CI runs against `MOODLE_403_STABLE` and `MOODLE_405_STABLE`. Do not claim
automated CI on Moodle 5.0 unless a 5.0 CI job is added and passing.
```

- [ ] **Step 3: Update docs/local-dev-testing.md — note Moodle 5.0.8**

In `docs/local-dev-testing.md`, after the intro paragraph (after line 6), add:

```markdown
> **Tested Moodle versions:** Automated CI runs against Moodle 4.3 and 4.5.
> Local development and testing uses Moodle 5.0.8.
```

- [ ] **Step 4: Update docs/release-checklist.md — add hard-fail note and Moodle range**

In `docs/release-checklist.md`, replace step 3:
```markdown
3. **Fetch RDKit** — `scripts/update-rdkit-js.sh`.
```
with:
```markdown
3. **Fetch RDKit** — `scripts/update-rdkit-js.sh`. **Required before packaging** — `scripts/package-plugin.sh` will fail hard if these assets are missing. Pass `--without-rdkit` only for a local-only test build.
```

At the end of the file, after the last acceptance gate, add:

```markdown
## Supported Moodle range

- Supported: 4.3 through 5.0.x (`$plugin->supported = [403, 500]`)
- Automated CI: Moodle 4.3 and 4.5
- Local dev/testing: Moodle 5.0.8
- Do not claim automated CI on 5.0 unless a 5.0 CI job is added and passing
```

- [ ] **Step 5: Add CHANGELOG entry for 0.2.0**

In `CHANGELOG.md`, replace:
```markdown
## [Unreleased]
```
with:
```markdown
## [Unreleased]

## [0.2.0] - 2026-06-23

### Changed
- Renamed plugin display name from "ChemIllusion Study Cards" to **ChemIllusion Study Tools** throughout user-facing text and documentation.
- Aligned Moodle support documentation: range 4.3 through 5.0.x, automated CI on 4.3/4.5, local dev/testing on 5.0.8.

### Fixed
- Privacy API now fully covers `local_chemillusion_events` (metadata, context discovery, user listing, export, deletion).
- Privacy API now declares `pubchem_pug_rest` as an external location.
- `lookup_molecule` AJAX endpoint: `alt_types` return schema corrected from scalar to array.
- All top-level page scripts now use plugin-relative `require_once(__DIR__ . '/../../config.php')` instead of fragile `$_SERVER['DOCUMENT_ROOT']` bootstrap.
- Release packaging (`scripts/package-plugin.sh`) now fails hard when RDKit assets are missing; added `--without-rdkit` flag for local-only builds.
```

Also update the comparison link at the bottom:
```markdown
[0.2.0]: https://github.com/scottmreed/moodle-local-chemillusion/compare/v0.1.0...v0.2.0
```

- [ ] **Step 6: Commit**

```bash
cd /Users/scottreed/PycharmProjects/moodle-local-chemillusion
git add README.md docs/moodle-directory-submission.md docs/local-dev-testing.md docs/release-checklist.md CHANGELOG.md
git commit -m "docs: SaaS disclosure, Moodle version range, and submission checklist"
```

---

## Task 9: Final validation

**Files:** read-only checks, no edits

- [ ] **Step 1: Confirm no old naming survives in code/docs**

```bash
grep -R "ChemIllusion Study Cards\|Chemistry Study Tools" \
  /Users/scottreed/PycharmProjects/moodle-local-chemillusion \
  --include="*.php" --include="*.md" --include="*.mustache" \
  -n --exclude-dir=.git --exclude-dir=dist \
  | grep -v "chemillusion_moodle_phase_1a_1b_prd.md"
```

Expected: empty.

- [ ] **Step 2: Confirm no DOCUMENT_ROOT in PHP**

```bash
grep -R "DOCUMENT_ROOT\|moodleroot" \
  /Users/scottreed/PycharmProjects/moodle-local-chemillusion \
  --include="*.php" -n --exclude-dir=.git
```

Expected: empty.

- [ ] **Step 3: PHP lint entire plugin**

```bash
find /Users/scottreed/PycharmProjects/moodle-local-chemillusion \
  -name '*.php' \
  -not -path '*/vendor/*' \
  -not -path '*/dist/*' \
  -print0 | xargs -0 -n1 php -l 2>&1 | grep -v "No syntax errors"
```

Expected: empty (no syntax errors anywhere).

- [ ] **Step 4: Verify version.php is consistent**

```bash
grep -E "component|version|requires|supported|maturity|release" \
  /Users/scottreed/PycharmProjects/moodle-local-chemillusion/version.php
```

Expected output:
```
$plugin->component = 'local_chemillusion';
$plugin->version   = 2026062201;
$plugin->requires  = 2023100900; // Moodle 4.3.0 and later.
$plugin->supported = [403, 500]; // Moodle 4.3 through 5.0.
$plugin->maturity  = MATURITY_ALPHA;
$plugin->release   = '0.2.0';
```

If `$plugin->release` still says `'0.2.0'` update it in version.php; if it says `'0.1.0'` update to `'0.2.0'` and bump `$plugin->version` to `2026062202`.

- [ ] **Step 5: Verify git log looks clean**

```bash
cd /Users/scottreed/PycharmProjects/moodle-local-chemillusion && git log --oneline -8
```

Expected: 7 commits (one per task 1–7) plus the original HEAD, all with meaningful messages.
