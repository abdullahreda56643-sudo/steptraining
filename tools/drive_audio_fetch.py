import html
import json
import re
import sys
import urllib.request
from pathlib import Path
from urllib.parse import urlencode

if hasattr(sys.stdout, "reconfigure"):
    sys.stdout.reconfigure(encoding="utf-8")


HEADERS = {"User-Agent": "Mozilla/5.0"}


def fetch_text(url):
    request = urllib.request.Request(url, headers=HEADERS)
    with urllib.request.urlopen(request, timeout=60) as response:
        return response.read().decode("utf-8", errors="replace")


def safe_name(name, default):
    name = html.unescape(name)
    name = re.sub(r'[<>:"/\\|?*\x00-\x1f]', "-", name).strip()
    return name or default


def folder_entries(folder_id):
    url = "https://drive.google.com/embeddedfolderview?" + urlencode({"id": folder_id})
    source = fetch_text(url)
    entries = []
    for block in re.findall(r'<div class="flip-entry"[^>]*>(.*?)(?=<div class="flip-entry"|</div></div></div></body>)', source, re.S):
        folder_match = re.search(r'<a href="https://drive\.google\.com/drive/folders/([A-Za-z0-9_-]+)', block)
        file_match = re.search(r'<a href="https://drive\.google\.com/file/d/([A-Za-z0-9_-]+)', block)
        name_match = re.search(r'class="flip-entry-title">([^<]+)</div>', block, re.S)
        if not name_match:
            continue
        name = html.unescape(name_match.group(1)).strip()
        if folder_match:
            entries.append({"type": "folder", "id": folder_match.group(1), "name": name})
        elif file_match and re.search(r"\.(?:mp3|wav|m4a|ogg)$", name, re.I):
            entries.append({"type": "file", "id": file_match.group(1), "name": name})
    return entries


def download_file(file_id, destination):
    url = "https://drive.google.com/uc?" + urlencode({"export": "download", "id": file_id})
    opener = urllib.request.build_opener()
    opener.addheaders = list(HEADERS.items())
    request = opener.open(url, timeout=120)
    content = request.read()
    if len(content) < 100 or b"<html" in content[:500].lower():
        raise RuntimeError("تعذر تنزيل الملف مباشرة من Google Drive.")
    destination.write_bytes(content)


def main():
    if len(sys.argv) < 3:
        print(json.dumps({"ok": False, "error": "معلمات Google Drive غير مكتملة."}))
        return
    folder_id = sys.argv[1].strip()
    output_dir = Path(sys.argv[2])
    result_file = Path(sys.argv[3]) if len(sys.argv) > 3 else None
    output_dir.mkdir(parents=True, exist_ok=True)
    downloaded = []
    visited = set()
    pending = [(folder_id, "")]

    try:
        while pending:
            current_id, relative_path = pending.pop(0)
            if current_id in visited:
                continue
            visited.add(current_id)
            for entry in folder_entries(current_id):
                if entry["type"] == "folder":
                    pending.append((entry["id"], relative_path + safe_name(entry["name"], "folder") + "/"))
                    continue
                extension = Path(entry["name"]).suffix.lower()
                if extension not in (".mp3", ".wav", ".m4a", ".ogg"):
                    continue
                stored_name = safe_name(relative_path.replace("/", "-") + entry["name"], "audio" + extension)
                stored_path = output_dir / stored_name
                download_file(entry["id"], stored_path)
                downloaded.append({
                    "name": entry["name"],
                    "stored_name": stored_name,
                    "size": stored_path.stat().st_size,
                })
    except Exception as error:
        result = {"ok": False, "error": str(error), "files": downloaded}
        text = json.dumps(result, ensure_ascii=True)
        print(text)
        if result_file:
            result_file.write_text(text, encoding="utf-8")
        return

    text = json.dumps({"ok": True, "files": downloaded}, ensure_ascii=True)
    print(text)
    if result_file:
        result_file.write_text(text, encoding="utf-8")


if __name__ == "__main__":
    main()
