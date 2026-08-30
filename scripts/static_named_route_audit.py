#!/usr/bin/env python3
"""Best-effort static audit of literal route('name') calls against routes/*.php.
No Composer/vendor bootstrap required. Intended as a release smoke check, not a
replacement for Laravel's `route:list` / feature tests.
"""
from pathlib import Path
import re, sys

ROOT = Path(__file__).resolve().parents[1]
route_files = sorted((ROOT / 'routes').glob('*.php'))

# Maintain name-prefix groups using rough brace-depth tracking. Laravel route files
# in this project keep group(function () { on the same statement, which makes this
# reliable for our literal route-name smoke check.
def parse_names(path: Path):
    names=set(); stack=[]; depth=0; pending=None; pending_prefix=''
    lines=path.read_text(errors='ignore').splitlines()
    for raw in lines:
        line=re.sub(r'//.*$', '', raw)
        prefix=''.join(x[1] for x in stack)

        # Name-prefix groups in this project declare ->name(...)->group(function on
        # the opening statement. Track them independently from route declarations.
        group_match=None
        if 'group(function' in line or 'group(static function' in line:
            m=list(re.finditer(r"->name\(\s*['\"]([^'\"]+)['\"]\s*\)", line))
            if m:
                group_match=m[-1].group(1)

        # Closure-backed routes can contain semicolons inside the callback, so also
        # capture their final })->name(...) directly.
        if '})->name' in line:
            for m in re.finditer(r"->name\(\s*['\"]([^'\"]+)['\"]\s*\)", line):
                names.add(prefix + m.group(1))

        # Individual Route declarations may span multiple lines. Buffer them until
        # their semicolon so chained ->where()->name() calls are included.
        stripped=line.strip()
        if pending is None and re.search(r'\bRoute::(?:get|post|put|patch|delete|options|any|match|view|redirect)\s*\(', stripped):
            pending=stripped
            pending_prefix=prefix
        elif pending is not None:
            pending += ' ' + stripped

        if pending is not None and ';' in stripped:
            for m in re.finditer(r"->name\(\s*['\"]([^'\"]+)['\"]\s*\)", pending):
                names.add(pending_prefix + m.group(1))
            pending=None; pending_prefix=''

        opens=line.count('{'); closes=line.count('}')
        if group_match is not None and opens:
            stack.append((depth + opens, group_match))
        depth += opens - closes
        while stack and depth < stack[-1][0]:
            stack.pop()
    return names

names=set()
for f in route_files:
    names |= parse_names(f)

refs={}
scan_roots=[ROOT/'app', ROOT/'resources', ROOT/'config']
pat=re.compile(r"(?<!->)\broute\(\s*['\"]([^'\"]+)['\"]")
for base in scan_roots:
    if not base.exists(): continue
    for p in base.rglob('*'):
        if not p.is_file() or p.suffix not in {'.php', '.blade.php'}: continue
        text=p.read_text(errors='ignore')
        for m in pat.finditer(text):
            refs.setdefault(m.group(1), set()).add(str(p.relative_to(ROOT)))

# Route names that are framework/vendor-provided in a normal Laravel install can be
# added here only if they are intentionally not declared in project route files.
allow=set()
missing={name: sorted(paths) for name,paths in refs.items() if name not in names and name not in allow}
print(f"Defined route names (static): {len(names)}")
print(f"Literal route() references: {len(refs)}")
if missing:
    print(f"Potential missing route names: {len(missing)}")
    for name,paths in sorted(missing.items()):
        print(f"- {name}: {', '.join(paths[:5])}")
    sys.exit(1)
print("Static named-route audit: PASS (0 missing literal route names)")
