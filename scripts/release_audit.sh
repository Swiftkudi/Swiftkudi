#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")/.."

echo "[1/7] PHP syntax"
find app config database routes tests -type f -name '*.php' -print0 | xargs -0 -n1 php -l >/tmp/swiftkudi_php_lint.txt
echo "  $(grep -c '^No syntax errors detected' /tmp/swiftkudi_php_lint.txt) PHP files passed"

echo "[2/7] Controller route targets"
python3 scripts/static_controller_route_audit.py

echo "[3/7] Named route references"
python3 scripts/static_named_route_audit.py

echo "[4/7] Blade template structure"
python3 scripts/static_blade_structure_audit.py

echo "[5/7] Shipped JavaScript syntax"
node --check public/js/app.js
node --check public/sw.js

echo "[6/7] Marketplace CSS delimiter sanity"
python3 - <<'PY'
from pathlib import Path
import re,sys
for name in ('public/css/marketplace.css','resources/css/marketplace.css'):
    text=re.sub(r'/\*.*?\*/','',Path(name).read_text(),flags=re.S)
    if text.count('{') != text.count('}'):
        raise SystemExit(f'{name}: unbalanced braces')
    print(f'  {name}: PASS')
PY

echo "[7/7] Sensitive deployment files"
if find . -type f \( -name '.env' -o -name '*.pem' -o -name '*.key' -o -name 'id_rsa' -o -name '*.p12' \) -print -quit | grep -q .; then
  echo "Sensitive runtime/key file detected in release tree" >&2
  exit 1
fi

echo "Release static audit: PASS"
