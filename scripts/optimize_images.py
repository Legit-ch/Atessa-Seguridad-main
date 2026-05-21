from pathlib import Path
from PIL import Image

root = Path('assets/img')
processed = []
errors = []

for path in root.rglob('*.jpg'):
    try:
        orig = path.stat().st_size
        with Image.open(path) as img:
            rgb = img.convert('RGB')
            rgb.save(path, format='JPEG', quality=80, optimize=True, progressive=True)
        new = path.stat().st_size
        processed.append((path, orig, new))
    except Exception as e:
        errors.append((path, str(e)))

for p, orig, new in sorted(processed, key=lambda x: x[1]-x[2], reverse=True):
    savings = (orig - new) * 100 / orig if orig else 0
    print(f"{p}: {orig//1024}KB -> {new//1024}KB ({savings:.1f}% savings)")

if errors:
    print('\nErrors:')
    for p, err in errors:
        print(p, err)
