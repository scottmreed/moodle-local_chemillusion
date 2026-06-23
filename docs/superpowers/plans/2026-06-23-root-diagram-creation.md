# Root Diagram Creation Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add a root-page diagram gallery whose links open focused, prefilled graphical-card editors with immediate previews.

**Architecture:** A new `diagram_tool_catalog` is the single source of truth for tool metadata and defaults. The dashboard exports its enabled tools, while `card_edit.php` validates the requested tool and passes focused custom data into the existing Moodle form. The AMD editor renders live previews from the same form values without changing existing persistence tables.

**Tech Stack:** Moodle PHP forms and renderables, Mustache, Bootstrap-compatible CSS, classic AMD JavaScript, PHPUnit.

**Execution constraint:** Do not create a worktree or commit changes.

---

### Task 1: Diagram Tool Catalog

**Files:**
- Create: `classes/cards/diagram_tool_catalog.php`
- Create: `tests/phpunit/diagram_tool_catalog_test.php`

- [ ] **Step 1: Write failing catalog tests**

Test that the catalog returns the four tools in display order, uses `ClCCCCBr`, `butane_anti`, `one_step_exergonic`, and `ethene_pi_bond` as defaults, and returns `null` for an unknown tool.

- [ ] **Step 2: Run the focused test and verify failure**

Run the Moodle PHPUnit test for `diagram_tool_catalog_test`. Expect failure because the class does not exist.

- [ ] **Step 3: Implement the catalog**

Create a final class with `get_all()`, `get(string $id)`, and `get_editor_defaults(string $id)`. Use existing feature flags to omit disabled graphical types, and construct editor URLs with validated `tool` query parameters.

- [ ] **Step 4: Re-run the focused test**

Expect all catalog tests to pass.

### Task 2: Root Tool Gallery

**Files:**
- Modify: `classes/output/dashboard_page.php`
- Modify: `templates/dashboard.mustache`
- Modify: `lang/en/local_chemillusion.php`
- Modify: `styles.css`
- Create: `pix/tools/newman-projection.svg`
- Create: `pix/tools/reaction-coordinate.svg`

- [ ] **Step 1: Extend catalog tests for template-ready fields**

Assert each dashboard tool has an id, title, description, URL, illustration type, and accessible image label.

- [ ] **Step 2: Verify the new assertions fail**

Run the focused catalog test and confirm the missing export fields cause the failure.

- [ ] **Step 3: Export and render the gallery**

Have `dashboard_page` expose `diagram_tools` only when graphical cards are enabled. Render a four-tile gallery before existing navigation, using shipped orbital imagery, copied sample SVGs for Newman/reaction-coordinate, and a styled SMILES sample for molecule structure.

- [ ] **Step 4: Add responsive styles and language strings**

Use a two-column desktop grid and one-column mobile layout. Keep tile copy to a title and one short line. Add visible focus, hover, and selected affordances without changing Moodle's page chrome.

- [ ] **Step 5: Run PHP lint and focused PHPUnit tests**

Expect clean PHP syntax and passing catalog tests.

### Task 3: Focused Editor Defaults

**Files:**
- Modify: `card_edit.php`
- Modify: `classes/form/graphical_card_form.php`
- Modify: `tests/phpunit/diagram_tool_catalog_test.php`
- Modify: `lang/en/local_chemillusion.php`

- [ ] **Step 1: Write failing default-shape tests**

Assert each tool default maps to the exact form keys required by the current persistence switch, including expanded reaction points and orbital template id.

- [ ] **Step 2: Verify the tests fail**

Run the focused test and confirm the catalog does not yet provide complete form defaults.

- [ ] **Step 3: Implement validated direct-tool initialization**

Read `tool` with `PARAM_ALPHANUMEXT`, resolve it through the catalog, and ignore unknown/disabled values. Pass fixed type, title, preset options, and preview data through Moodle form custom data. Preserve existing edit behavior.

- [ ] **Step 4: Reduce initial form controls**

For a fixed direct tool, use a hidden card type, prefill the card name, and build only the relevant section. Make preset/profile selection the primary choice for Newman, reaction-coordinate, and orbital tools. Place teacher note, raw JSON, functional-group id, and atom index in an advanced section.

- [ ] **Step 5: Preserve storage compatibility**

Save reaction template points from validated registry data, and save orbital `template_id`, description, SMILES, and optional atom index. Continue storing existing Newman and molecule shapes.

- [ ] **Step 6: Run focused tests and PHP lint**

Expect all tests to pass and no syntax errors.

### Task 4: Live Preview

**Files:**
- Modify: `amd/src/graphical_card_app.js`
- Modify: `styles.css`
- Generate: `amd/build/graphical_card_app.min.js`

- [ ] **Step 1: Add a preview contract to the form markup**

Render one stable `[data-region="diagram-preview"]` container and encode the fixed tool id and validated preset data as data attributes or hidden fields.

- [ ] **Step 2: Implement one preview dispatcher**

Build preview data from the visible fields and call the existing molecule, Newman, reaction-coordinate, or orbital renderer. Update on primary input and preset changes. Render the valid default immediately.

- [ ] **Step 3: Keep advanced inputs synchronized**

When a preset changes, populate substituents, rotation, reaction points, orbital SMILES, title, and teacher note before rendering.

- [ ] **Step 4: Build AMD output**

Run `scripts/build-js.sh` and confirm `amd/build/graphical_card_app.min.js` exists.

- [ ] **Step 5: Run JavaScript syntax checks**

Run `node --check` on the source and built module. Expect no syntax errors.

### Task 5: End-To-End Verification

**Files:**
- Modify as required by failures found during verification.

- [ ] **Step 1: Run repository checks**

Run all PHP lint checks, focused PHPUnit tests in the Moodle harness when available, and the local CI script.

- [ ] **Step 2: Purge Moodle caches**

Use the local Moodle harness cache purge command so updated Mustache, strings, and AMD modules are served.

- [ ] **Step 3: Verify desktop behavior**

Open `http://127.0.0.1:8100/local/chemillusion/`, confirm the gallery is above navigation, and open all four tools. Verify each editor starts with a complete diagram and only relevant primary controls.

- [ ] **Step 4: Verify responsive behavior**

Check desktop and mobile screenshots for text overflow, overlapping controls, blank previews, keyboard focus, and usable one-column layout.

- [ ] **Step 5: Review final diff**

Confirm no unrelated changes, no worktree, and no commit.
