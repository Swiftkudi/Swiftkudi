#!/usr/bin/env python3
"""Lightweight static balance audit for Blade block directives.

This does not compile Blade; it catches the most common template regressions such as
an @auth closed by @endif or a missing @endsection without requiring vendor packages.
"""
from pathlib import Path
import re
import sys

ROOT = Path(__file__).resolve().parents[1]
VIEW_ROOT = ROOT / 'resources' / 'views'

PAIRS = [
    ('if', 'endif'), ('foreach', 'endforeach'), ('for', 'endfor'), ('while', 'endwhile'),
    ('forelse', 'endforelse'), ('auth', 'endauth'), ('guest', 'endguest'),
    ('can', 'endcan'), ('cannot', 'endcannot'), ('canany', 'endcanany'),
    ('isset', 'endisset'), ('unless', 'endunless'), ('switch', 'endswitch'),
    ('push', 'endpush'), ('prepend', 'endprepend'), ('once', 'endonce'),
    ('production', 'endproduction'), ('env', 'endenv'),
]


def count(text: str, directive: str) -> int:
    return len(re.findall(r'@' + re.escape(directive) + r'\b', text))


def main() -> int:
    files = sorted(VIEW_ROOT.rglob('*.blade.php'))
    errors = []
    for path in files:
        text = path.read_text(errors='ignore')
        text = re.sub(r'\{\{--.*?--\}\}', '', text, flags=re.S)

        for opener, closer in PAIRS:
            opens, closes = count(text, opener), count(text, closer)
            if opens != closes:
                errors.append(f'{path.relative_to(ROOT)}: @{opener}={opens}, @{closer}={closes}')

        # @empty($value) is a block directive. Bare @empty is the @forelse branch.
        empty_blocks = len(re.findall(r'@empty\s*\(', text))
        endempty = count(text, 'endempty')
        if empty_blocks != endempty:
            errors.append(f'{path.relative_to(ROOT)}: block @empty={empty_blocks}, @endempty={endempty}')

        # A block section has exactly one quoted name argument: @section('content').
        block_sections = len(re.findall(r"@section\s*\(\s*(['\"])[^'\"]+\1\s*\)", text))
        section_closers = count(text, 'endsection') + count(text, 'show') + count(text, 'stop')
        if block_sections != section_closers:
            errors.append(
                f'{path.relative_to(ROOT)}: block @section={block_sections}, '
                f'closures={section_closers}'
            )

    print(f'Blade views checked: {len(files)}')
    if errors:
        print('Static Blade structure audit: FAIL')
        for error in errors:
            print(' - ' + error)
        return 1
    print('Static Blade structure audit: PASS (0 imbalances)')
    return 0


if __name__ == '__main__':
    sys.exit(main())
