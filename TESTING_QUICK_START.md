# ChemIllusion Plugin — Quick Testing Guide

## One-Time Setup

```bash
# From the plugin root:
cd /Users/scottreed/PycharmProjects/moodle-local-chemillusion

# Run setup
./testing/setup-local.sh

# Verify prerequisites
which mdk && docker info > /dev/null && /opt/homebrew/opt/php@8.3/bin/php --version
```

## Development Workflow

### Start Dev Server (blocking)
```bash
/opt/homebrew/opt/php@8.3/bin/php -S 127.0.0.1:8100 -t ~/moodles/mymoodle/moodle
```

### Start Dev Server (background)
```bash
/opt/homebrew/opt/php@8.3/bin/php -S 127.0.0.1:8100 -t ~/moodles/mymoodle/moodle &
sleep 1 && open http://127.0.0.1:8100/
```

### Kill Background Server
```bash
pkill -f "php -S 127.0.0.1:8100"
```

### Quick Cycle (after code changes)
```bash
# Kill server, clear cache, restart
pkill -f "php -S 127.0.0.1:8100" 2>/dev/null || true
cd ~/moodles/mymoodle/moodle && php admin/cli/purge_caches.php
/opt/homebrew/opt/php@8.3/bin/php -S 127.0.0.1:8100 -t ~/moodles/mymoodle/moodle &
sleep 1 && open http://127.0.0.1:8100/
```

### Rebuild JavaScript (after source changes)
```bash
bash scripts/build-js.sh
# Then run quick cycle above
```

### Auto-Restart on File Changes (requires `inotifywait`)
```bash
while true; do
  inotifywait -r -e modify \
    /Users/scottreed/PycharmProjects/moodle-local-chemillusion/classes \
    /Users/scottreed/PycharmProjects/moodle-local-chemillusion/amd \
    /Users/scottreed/PycharmProjects/moodle-local-chemillusion/templates 2>/dev/null && {
    echo "📝 Changes detected, restarting..."
    pkill -f "php -S 127.0.0.1:8100" 2>/dev/null || true
    cd ~/moodles/mymoodle/moodle && php admin/cli/purge_caches.php
    /opt/homebrew/opt/php@8.3/bin/php -S 127.0.0.1:8100 -t ~/moodles/mymoodle/moodle &
    sleep 1
    echo "✓ Server restarted at http://127.0.0.1:8100/"
  }
done
```

## Testing Checklist

### Navigation
- [ ] Dashboard shows 4 nav items (Molecule lookup, Study decks, Account linking, Privacy)
- [ ] Each nav item has description text
- [ ] Back buttons appear on Molecule Lookup and Study Decks pages

### Molecule Lookup
- [ ] Search for "CCO" (ethanol)
- [ ] Search for "c1ccccc1" (benzene SMILES)
- [ ] Verify 2D structure renders (RDKit SVG)
- [ ] Verify metadata (formula, MW, CID)
- [ ] "Open in ChemIllusion" button appears
- [ ] PubChem link works (opens in new tab)
- [ ] SMILES-only lookup (if input has SMILES characters, no text fallback)

### Study Decks
- [ ] "Create one to start studying" button is clickable
- [ ] Click button → form appears (deck name + molecule list)
- [ ] Enter "My Deck" and "CCO\nBenzene" 
- [ ] Click "Save deck" → resolves molecules → saves → reloads page
- [ ] Deck appears in list with card count
- [ ] Click deck name → opens flashcard view

### Full Reset
```bash
docker exec pgsql psql -U postgres -c "DROP DATABASE IF EXISTS mymoodle"
rm -rf ~/moodles/mymoodle
cd /Users/scottreed/PycharmProjects/moodle-local-chemillusion
./testing/setup-local.sh
```

## Common Issues

**Port 8100 already in use?**
```bash
lsof -i :8100
pkill -9 -f "php -S 127.0.0.1:8100"
```

**Changes not showing up?**
```bash
# Always clear cache when you change PHP
pkill -f "php -S 127.0.0.1:8100" 2>/dev/null || true
cd ~/moodles/mymoodle/moodle && php admin/cli/purge_caches.php
/opt/homebrew/opt/php@8.3/bin/php -S 127.0.0.1:8100 -t ~/moodles/mymoodle/moodle &
```

**RDKit not rendering molecules?**
```bash
# Check RDKit WASM files exist
ls amd/src/rdkit_molecule_renderer.js
ls thirdparty/rdkit-js/

# Check browser console (F12) for errors
# Verify rdkit_enabled=true in tools.php page source
```

**JavaScript not updating?**
```bash
# Rebuild minified JS
bash scripts/build-js.sh

# Clear Moodle cache
cd ~/moodles/mymoodle/moodle && php admin/cli/purge_caches.php

# Hard refresh in browser (Cmd+Shift+R on Mac, Ctrl+Shift+R on Linux/Windows)
```
