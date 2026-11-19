"""Build a deterministic metadata migration from a SHA-verified live FTP inventory."""
import argparse
import json
import re
from datetime import datetime, timezone
from pathlib import Path

parser = argparse.ArgumentParser()
parser.add_argument('--inventory', required=True)
parser.add_argument('--output', required=True)
args = parser.parse_args()
inventory = json.loads(Path(args.inventory).read_text(encoding='utf-8'))
assets = sorted(inventory['files'], key=lambda row: row['file_path'])
issues = []
seen = set()
for row in assets:
    path = row['file_path']
    if path in seen or 'delvallenotify' in path.lower():
        raise ValueError('Duplicate or unlisted path in inventory')
    seen.add(path)
    if not re.fullmatch('[a-f0-9]{64}', row.get('sha256', '')):
        raise ValueError('Every hosted file must have a verified SHA-256')
    match = re.fullmatch(r'newsletters/(\d{4})/(?:tvsnews(\d{2})(\d{2})\.pdf|(\d{2})(\d{2})/index\.html)', path)
    if match:
        year, month, shortyear = int(match[1]), int(match[2] or match[4]), int(match[3] or match[5])
        if year % 100 != shortyear or not 1 <= month <= 12:
            raise ValueError('Newsletter path has an inconsistent date')
        issues.append({'year': year, 'month': month, 'file_path': path,
                       'file_type': 'pdf' if path.endswith('.pdf') else 'html', 'file_size': row['file_size']})
if len({(row['year'], row['month']) for row in issues}) != len(issues):
    raise ValueError('Multiple newsletter files claim the same issue; reconcile before migrating')
manifest = {'version': 1, 'migration_id': 'legacy-live-ftp-20261115',
            'imported_at_utc': datetime.now(timezone.utc).strftime('%Y-%m-%d %H:%M:%S'),
            'source': 'https://www.trivalleystargazers.org/',
            'assets': assets, 'newsletters': sorted(issues, key=lambda row: (row['year'], row['month']))}
Path(args.output).write_text(json.dumps(manifest, ensure_ascii=True, indent=2)+'\n', encoding='utf-8')
print(f"Generated {len(assets)} archive assets and {len(issues)} newsletter issues")
