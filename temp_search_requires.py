import pathlib
import re

pattern = re.compile(r"require(_once)?\s*\(?\s*['\"][^'\"]*\.\./\.\./")
root = pathlib.Path('modules')
entries = []
for path in sorted(root.rglob('*.php')):
    text = path.read_text(encoding='utf-8', errors='ignore')
    matches = list(pattern.finditer(text))
    if matches:
        first = matches[0]
        line_start = text.rfind('\n', 0, first.start())
        line_end = text.find('\n', first.start())
        if line_start == -1:
            line_start = 0
        else:
            line_start += 1
        if line_end == -1:
            line_end = len(text)
        first_line = text[line_start:line_end].strip()
        entries.append((str(path.resolve()), first_line, len(matches)))
for path, line, count in entries:
    print(f"{path}|{line}|{count}")
