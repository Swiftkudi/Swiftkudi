#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")/.."

echo "[1/6] PHP syntax"
find app config database routes tests -type f -name '*.php' -print0 | xargs -0 -n1 php -l >/tmp/swiftkudi_php_lint.txt
echo "  $(grep -c '^No syntax errors detected' /tmp/swiftkudi_php_lint.txt) PHP files passed"

echo "[2/6] Controller route targets"
python3 scripts/static_controller_route_audit.py

echo "[3/6] Named route references"
python3 scripts/static_named_route_audit.py

echo "[4/6] Shipped JavaScript syntax"
node --check public/js/app.js
node --check public/sw.js

echo "[5/6] Marketplace CSS delimiter sanity"
python3 - <<'PY'
from pathlib import Path
import re,sys
for name in ('public/css/marketplace.css','resources/css/marketplace.css'):
    text=re.sub(r'/\*.*?\*/','',Path(name).read_text(),flags=re.S)
    if text.count('{') != text.count('}'):
        raise SystemExit(f'{name}: unbalanced braces')
    print(f'  {name}: PASS')
PY

echo "[6/6] Sensitive deployment files"
if find . -type f \( -name '.env' -o -name '*.pem' -o -name '*.key' -o -name 'id_rsa' -o -name '*.p12' \) -print -quit | grep -q .; then
  echo "Sensitive runtime/key file detected in release tree" >&2
  exit 1
fi

echo "Release static audit: PASS"
