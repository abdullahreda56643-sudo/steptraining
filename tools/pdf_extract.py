import json
import re
import sys
from pathlib import Path

vendor_dir = Path(__file__).resolve().parent / "pdf_vendor"
if vendor_dir.is_dir():
    sys.path.insert(0, str(vendor_dir))

if hasattr(sys.stdout, "reconfigure"):
    sys.stdout.reconfigure(encoding="utf-8")

try:
    from pypdf import PdfReader
except ImportError:
    print(json.dumps({"ok": False, "error": "مكتبة استخراج PDF غير مثبتة."}), file=sys.stdout)
    raise SystemExit(1)


def normalize(text):
    return re.sub(r"\s+", " ", text or "").strip()


def arabic_text(text):
    arabic = re.findall(r"[\u0600-\u06FF][\u0600-\u06FF\s0-9.,؛:()_-]*", text or "", re.UNICODE)
    cleaned = normalize(" ".join(arabic))
    cleaned = re.sub(r"\b\d{1,3}\b", "", cleaned)
    return normalize(cleaned)


def classify(text, filename):
    haystack = normalize(filename + " " + text[:3000]).lower()
    if re.search(r"\brecording\b|استماع|Listening", haystack):
        return "questions"
    if any(word in haystack for word in ("synonym", "مترادف", "vocab", "مفرد")):
        return "vocab"
    if re.search(r"\bgrammar\b|قرامر|grammar", haystack):
        return "grammar"
    return "passages"


def extract_vocab(text):
    items = []
    seen = set()
    for match in re.finditer(
        r"(?<![A-Za-z])(?:\d+\s*[-.)]\s*)?([A-Za-z][A-Za-z' -]{1,50}?)\s*=\s*([^=\n]{2,160}?)(?:\s+(?:\d+\s*[-.)]\s*)?[A-Za-z][A-Za-z' -]{1,50}?\s*=|$)",
        text,
        re.MULTILINE,
    ):
        word = normalize(match.group(1))
        meaning = normalize(match.group(2))
        if len(word) < 2 or len(meaning) < 2:
            continue
        key = re.sub(r"[^\w]+", "", word.lower())
        if key in seen:
            continue
        seen.add(key)
        arabic_meaning = arabic_text(meaning)
        source_meaning = arabic_meaning if arabic_meaning else ""
        items.append({
            "word": word[:255],
            "meaning_ar": source_meaning[:2000],
            "synonym": "",
            "antonym": "",
            "example": "",
            "confidence": 92,
        })
    return items


def extract_questions(text):
    items = []
    sections = re.split(r"(?im)(?=^[^\r\n]{2,90}\s*[-–]\s*Listening(?:\s+Questions)?\s*$)", text)
    if len(sections) <= 1:
        sections = re.split(r"(?im)(?=^[^\r\n]{2,90}\s*Listening(?:\s+Questions)?\s*$)", text)
    for section in sections:
        heading_match = re.match(r"\s*([^\r\n]{2,90}?)\s*(?:[-–]\s*)?Listening(?:\s+Questions)?\s*$", section, re.I | re.M)
        group = normalize(heading_match.group(1)) if heading_match else ""
        starts = list(re.finditer(r"(?im)^Q\s*(\d{1,3})?\s*[:.)-]\s*(.+?)(?:\r?\n|$)", section))
        if not starts:
            continue
        for index, match in enumerate(starts):
            start = match.start()
            end = starts[index + 1].start() if index + 1 < len(starts) else len(section)
            block = section[start:min(end, start + 1800)]
            question = normalize(match.group(2))
            options = {}
            for option_match in re.finditer(r"(?im)^\s*([A-D])\s*[.)-]\s*(.+?)\s*$", block):
                option = normalize(option_match.group(2))
                if option and not re.match(r"^Q\s*\d+", option, re.I):
                    options[option_match.group(1).upper()] = option[:1000]
            if len(question) < 5 or len(options) < 2:
                continue
            items.append({
                "text": question[:5000],
                "option_a": options.get("A", ""),
                "option_b": options.get("B", ""),
                "option_c": options.get("C", ""),
                "option_d": options.get("D", ""),
                "correct": "A",
                "explanation_ar": "تم استخراج هذا السؤال من ملف PDF ويحتاج مراجعة الإجابة الصحيحة.",
                "confidence": 88,
                "group": group[:255],
            })
        key_match = re.search(r"(?m)^(?:Q\s*\d+\s*)+(?:\r?\n)?([A-D](?:\s+[A-D]){1,9})\s*$", section, re.I)
        if key_match:
            answers = re.findall(r"[A-D]", key_match.group(1), re.I)
        else:
            standalone = re.search(r"(?m)^([A-D](?:\s+[A-D]){1,9})\s*$", section, re.I)
            answers = re.findall(r"[A-D]", standalone.group(1), re.I) if standalone else []
        if answers:
            grouped_items = [item for item in items if item.get("group") == group]
            for answer_index, answer in enumerate(answers):
                if answer_index < len(grouped_items):
                    grouped_items[answer_index]["correct"] = answer.upper()
    if items:
        return items

    starts = list(re.finditer(r"(?m)^(\d{1,3})[.)]?\s*(.+?)(?:\r?\n|$)", text))
    for index, match in enumerate(starts):
        start = match.start()
        end = starts[index + 1].start() if index + 1 < len(starts) else len(text)
        block_end = min(end, start + 1500)
        block = text[start:block_end]
        lines = [normalize(line) for line in block.splitlines() if normalize(line)]
        if not lines:
            continue
        question = re.sub(r"^\d{1,3}[.)]?\s*", "", lines[0], count=1).strip()
        options = {}
        for option_match in re.finditer(r"(?m)^([A-D])\s*[-.)]\s*(.+)$", block):
            options[option_match.group(1)] = normalize(option_match.group(2))
        if len(question) > 5 and len(options) >= 2:
            items.append({
                "text": question[:5000],
                "option_a": options.get("A", ""),
                "option_b": options.get("B", ""),
                "option_c": options.get("C", ""),
                "option_d": options.get("D", ""),
                "correct": "A",
                "explanation_ar": "تم استخراج هذا السؤال من ملف PDF ويحتاج مراجعة الإجابة الصحيحة.",
                "confidence": 88,
            })
    return items


def main():
    if len(sys.argv) < 2:
        print(json.dumps({"ok": False, "error": "لم يتم تحديد ملف."}))
        return

    pdf_path = Path(sys.argv[1])
    if not pdf_path.is_file():
        print(json.dumps({"ok": False, "error": "الملف غير موجود."}))
        return

    pages = []
    reader = PdfReader(str(pdf_path))
    for page in reader.pages:
        pages.append(page.extract_text() or "")
    text = "\n\n".join(pages)
    embedded_audio = []
    output_dir = Path(sys.argv[2]) if len(sys.argv) > 2 else None
    if output_dir:
        output_dir.mkdir(parents=True, exist_ok=True)
    try:
        attachments = reader.attachments
        for attachment_name, attachment_parts in attachments.items():
            for attachment_index, attachment_data in enumerate(attachment_parts):
                extension = Path(str(attachment_name)).suffix.lower()
                if extension not in (".mp3", ".wav", ".m4a", ".ogg"):
                    continue
                safe_name = re.sub(r"[^A-Za-z0-9_.-]+", "-", str(attachment_name)).strip("-") or f"audio-{attachment_index + 1}{extension}"
                saved_name = f"embedded-{len(embedded_audio) + 1}-{safe_name}"
                if output_dir:
                    (output_dir / saved_name).write_bytes(attachment_data)
                embedded_audio.append(saved_name)
    except Exception:
        attachments = {}

    media_links = set()
    for page in reader.pages:
        for annotation_ref in page.get("/Annots", []):
            try:
                annotation = annotation_ref.get_object()
                action = annotation.get("/A")
                uri = annotation.get("/URI") or (action.get("/URI") if action else None)
                uri = str(uri or "").strip()
                if uri and re.search(r"https?://", uri, re.I):
                    media_links.add(uri)
            except Exception:
                continue
    media_links = sorted(media_links)
    document_type = classify(text, pdf_path.name)
    items = extract_vocab(text) if document_type == "vocab" else extract_questions(text) if document_type == "questions" else []
    print(json.dumps({
        "ok": True,
        "type": document_type,
        "pages": len(reader.pages),
        "text_length": len(text),
        "items": items,
        "embedded_audio": embedded_audio,
        "media_links": media_links,
    }, ensure_ascii=False))


if __name__ == "__main__":
    main()
