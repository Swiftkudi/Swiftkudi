#!/usr/bin/env python3
"""Static controller route target audit that does not require Composer/vendor."""
from pathlib import Path
import re, sys
ROOT=Path(__file__).resolve().parents[1]

# Build FQCN -> set(public method) from application controllers.
controllers={}
for p in (ROOT/'app/Http/Controllers').rglob('*.php'):
    text=p.read_text(errors='ignore')
    ns=re.search(r'\bnamespace\s+([^;]+);', text)
    cls=re.search(r'\bclass\s+(\w+)', text)
    if not ns or not cls: continue
    fq=ns.group(1).strip()+'\\'+cls.group(1)
    methods=set(re.findall(r'\bpublic\s+function\s+(\w+)\s*\(', text))
    controllers[fq]=methods

missing=[]; total=0
for route_file in sorted((ROOT/'routes').glob('*.php')):
    text=route_file.read_text(errors='ignore')
    imports={m.group(2):m.group(1)+'\\'+m.group(2) for m in re.finditer(r'^use\s+((?:App\\Http\\Controllers)(?:\\[A-Za-z0-9_]+)*)\\([A-Za-z0-9_]+);', text, re.M)}

    # [Controller::class, 'method'] including leading fully-qualified controller names.
    pat=re.compile(r"\[\s*(\\?App\\Http\\Controllers\\[A-Za-z0-9_\\]+|[A-Za-z0-9_]+)::class\s*,\s*['\"]([A-Za-z0-9_]+)['\"]\s*\]")
    for m in pat.finditer(text):
        token=m.group(1).lstrip('\\'); method=m.group(2); total+=1
        if token.startswith('App\\Http\\Controllers\\'):
            fq=token
        else:
            fq=imports.get(token)
        if not fq:
            missing.append((route_file.name, token, method, 'controller import could not be resolved'))
            continue
        methods=controllers.get(fq)
        if methods is None:
            missing.append((route_file.name, fq, method, 'controller class file not found'))
        elif method not in methods:
            missing.append((route_file.name, fq, method, 'public method not found'))

print(f'Controller-backed route targets checked: {total}')
if missing:
    print(f'Unresolved controller route targets: {len(missing)}')
    for file,fq,method,why in missing:
        print(f'- {file}: {fq}@{method} ({why})')
    sys.exit(1)
print('Static controller-route audit: PASS (0 unresolved targets)')
