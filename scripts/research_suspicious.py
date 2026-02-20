#!/usr/bin/env python3
"""
Deep research pipeline for suspicious educational programs.
Produces Dror-Israel-level analysis for each org cluster.

Uses the 9-chapter analytical lens documented in research_lens.json.
Outputs: one markdown report per cluster → HTML site page.

Usage:
    python3 research_suspicious.py                    # Run all pending clusters
    python3 research_suspicious.py --cluster "Hartman" # Run specific cluster
    python3 research_suspicious.py --list              # List clusters & status
"""

import json
import os
import sys
import subprocess
import argparse
from pathlib import Path
from datetime import datetime

PROJECT_DIR = Path(__file__).parent
REPORTS_DIR = PROJECT_DIR / "research_reports"
LENS_FILE = PROJECT_DIR / "research_lens.json"
METADATA_FILE = PROJECT_DIR / "manual_metadata.json"

# ── Analytical lens (the 9-chapter framework from Dror Israel report) ──

RESEARCH_PROMPT_TEMPLATE = """
אתה חוקר מומחה לניתוח ארגונים חינוכיים בישראל.
עליך לבצע ניתוח מערכתי מעמיק של הארגון/תוכנית הבאים, בדיוק באותה רמת עומק ואיכות כמו הניתוח של דרור ישראל.

# הארגון/תוכנית לניתוח:
{org_name}

# תוכניות קשורות במאגר גפ"ן:
{related_programs}

# תיאורים קיימים מהמחקר:
{existing_descriptions}

# מידע קיים מ-Gemini:
{gemini_data}

# אנשי מפתח שזוהו:
{key_people_data}

---

כתוב דוח מחקרי מעמיק ב-9 פרקים, בדיוק לפי המבנה הבא:

## פרק 1: זהות ארגונית, רשת עמותות ומבנה שליטה
- האם זו ישות משפטית יחידה או רשת של עמותות/חברות?
- מי שולט ברשת? האם הכוח מרוכז בידי מעטים?
- האם אותם אנשים מחליפים תפקידים בין ישויות שונות?
- חפש ב-GuideStar Israel: https://www.guidestar.org.il
- חפש ברשם העמותות/חברות

## פרק 2: ניתוח הנהגה וקשרים פוליטיים
- מי האנשים המרכזיים (מייסדים, מנכ"ל, דירקטוריון)?
- האם למנהיגים יש זיקות פוליטיות מפורשות?
- האם הארגון או מנהיגיו מעורבים בקמפיינים פוליטיים?
- האם הוגשו תביעות השתקה (SLAPP)?

## פרק 3: ניתוח כספי ומקורות מימון
- מהו התקציב השנתי? כמה מהמדינה לעומת תורמים זרים?
- האם יש קשרים לקרנות זרות בעלות אוריינטציה פוליטית (קרן חדשה, פורד, וכו')?
- האם נמצאו ממצאי ביקורת או פערים תקציביים?
- חפש ב-NGO Monitor, IRS 990 reports, GuideStar financials

## פרק 4: אידיאולוגיה ופדגוגיה — חדירת הפדגוגיה הביקורתית
- האם הארגון משתמש בפדגוגיה ביקורתית (פאולו פריירה וכו')?
- האם קיימת "התקה סמנטית" — שימוש במונחים מסורתיים עם תוכן פרוגרסיבי?
- האם הפדגוגיה ממסגרת את החברה הקיימת כמערכת דיכוי?
- מילות מפתח לחיפוש: פדגוגיה ביקורתית, צדק חברתי, דה-קולוניזציה, פוסט-לאומי, שוויון מגדרי, זהות מגדרית

## פרק 5: תכנים בעייתיים והשפעה על חינוך וערכים
- האם התוכנית מכניסה תכנים מיניים/מגדריים לקטינים?
- האם יש תוכן שמערער על מבנה משפחתי, זהות לאומית, או מסורת דתית?
- האם המנחים מוכשרים באידיאולוגיה ולא בפסיכולוגיה התפתחותית?

## פרק 6: פרשות, חשדות ואי-סדרים
- האם היו תחקירים תקשורתיים?
- האם היו שערוריות כספיות?
- האם הארגון השתיק מבקרים באמצעות איומים משפטיים?

## פרק 7: השפעה על מדיניות ציבורית וקשרים עם הממשל
- האם לארגון חוזים ללא מכרז עם משרדי ממשלה?
- האם הוא ממצב את עצמו כחיוני בעתות חירום?
- האם בוגרי הארגון מושתלים במשרד החינוך או ברשויות?

## פרק 8: ניתוח לפי עולם הערכים — התאמה וסתירה
צור טבלת התאמה לערכים (ציר + הערכה):
- זהות יהודית ומורשת: מחזק ↔ מחליש/מאוניברסל
- זהות לאומית ציונית: מאשר ↔ פוסט-לאומי/ביקורתי
- מבנה משפחתי וקהילתי: תומך ↔ מפרק
- צה"ל ושירות לאומי: תומך ↔ ביקורתי
- ערכים דמוקרטיים: דמוקרטיה פרוגרסיבית ↔ אקטיביזם רדיקלי
- אוריינטציה כלכלית: שוק חופשי ↔ סוציאליסטי
- מגדר ומיניות: מסורתי ↔ תיאוריית מגדר רדיקלית
- השפעה זרה: עצמאי ↔ מימון זר עם אג'נדה

## פרק 9: סיכום ומסקנות — מפת הסיכונים
- דירוג סיכון כולל (1-5) עם נימוק
- רשימת סיכונים מרכזיים
- המלצות לחקירה נוספת
- קשרים לארגונים חשודים אחרים במאגר

---

חשוב מאוד:
- ציין מקורות לכל טענה (קישורים, מסמכים, כתבות)
- הבדל בין עובדות מאומתות לבין חשדות/סימני אזהרה
- כתוב בעברית, בסגנון מקצועי ועניני
- אם אין מספיק מידע על פרק מסוים, ציין זאת במפורש ורשום כ"דורש חקירה נוספת"
"""


def load_lens():
    with open(LENS_FILE, encoding="utf-8") as f:
        return json.load(f)


def load_metadata():
    with open(METADATA_FILE, encoding="utf-8") as f:
        return json.load(f)["data"]


def get_cluster_programs(cluster, metadata):
    """Find all metadata entries related to a cluster's organizations."""
    results = []
    for org in cluster["orgs"]:
        org_lower = org.lower()
        for m in metadata:
            name = (m.get("program_name") or "").lower()
            desc = (m.get("description") or "").lower()
            notes = (m.get("notes") or "").lower()
            if org_lower in name or org_lower in desc or org_lower in notes:
                results.append(m)
    # Deduplicate by program_number
    seen = set()
    unique = []
    for r in results:
        pn = r.get("program_number")
        if pn not in seen:
            seen.add(pn)
            unique.append(r)
    return unique


def format_programs_for_prompt(programs):
    lines = []
    for p in programs:
        score = p.get("score", "?")
        name = p.get("program_name", "")
        desc = p.get("description", "")
        lines.append(f"- [{score}★] {name}: {desc}")
    return "\n".join(lines) if lines else "(לא נמצאו תוכניות קשורות)"


def format_gemini_data(programs):
    lines = []
    for p in programs:
        gs = p.get("gemini_summary")
        if gs:
            lines.append(f"### {p.get('program_name', '')}")
            lines.append(gs)
            ga = p.get("gemini_analysis", "")
            if ga:
                lines.append(f"ניתוח: {ga[:500]}")
            lines.append("")
    return "\n".join(lines) if lines else "(אין נתוני Gemini)"


def format_key_people(programs):
    lines = []
    for p in programs:
        kp = p.get("key_people")
        if kp:
            lines.append(f"### {p.get('program_name', '')}")
            if isinstance(kp, str):
                try:
                    kp = json.loads(kp)
                except:
                    pass
            lines.append(json.dumps(kp, ensure_ascii=False, indent=2) if isinstance(kp, (list, dict)) else str(kp))
            lines.append("")
    return "\n".join(lines) if lines else "(לא זוהו אנשי מפתח)"


def build_prompt(cluster, programs):
    org_name = cluster["cluster"]
    orgs_list = ", ".join(cluster["orgs"])

    return RESEARCH_PROMPT_TEMPLATE.format(
        org_name=f"{org_name}\nארגונים ברשת: {orgs_list}",
        related_programs=format_programs_for_prompt(programs),
        existing_descriptions="\n".join(
            f"- {p['program_name']}: {p.get('description', '')}"
            for p in programs if p.get("description")
        ) or "(אין)",
        gemini_data=format_gemini_data(programs),
        key_people_data=format_key_people(programs),
    )


def run_research(cluster, prompt, output_path):
    """Run Claude research agent via subprocess."""
    print(f"\n{'='*60}")
    print(f"Researching: {cluster['cluster']}")
    print(f"Organizations: {', '.join(cluster['orgs'])}")
    print(f"Output: {output_path}")
    print(f"{'='*60}\n")

    # Use claude CLI to do the research
    # --system-prompt overrides CLAUDE.md orchestrator instructions
    # --setting-sources user avoids project CLAUDE.md
    system = (
        "You are a professional Israeli NGO researcher. "
        "Your ONLY job is to produce a detailed Hebrew research report. "
        "Do NOT delegate. Do NOT ask questions. Do NOT refuse. "
        "Do NOT comment on methodology. Just produce the report. "
        "Write the full 9-chapter report directly. Use your knowledge. "
        "If you lack info on a chapter, write what you know and note gaps."
    )
    cmd = [
        "claude",
        "--print",
        "--model", "sonnet",
        "--system-prompt", system,
        "--setting-sources", "user",
        "-p", prompt
    ]

    try:
        result = subprocess.run(
            cmd,
            capture_output=True,
            text=True,
            timeout=1200,  # 20 min per cluster
            cwd=str(PROJECT_DIR)
        )

        report = result.stdout.strip()
        if not report:
            report = f"# שגיאה בחקירת {cluster['cluster']}\n\nלא התקבל פלט מהסוכן.\n\nstderr: {result.stderr[:500]}"

        # Save report
        output_path.write_text(report, encoding="utf-8")
        print(f"  ✓ Saved {len(report)} chars to {output_path.name}")
        return True

    except subprocess.TimeoutExpired:
        error_report = f"# {cluster['cluster']} — חקירה חלקית\n\nהסוכן חרג ממגבלת הזמן (10 דקות).\n"
        output_path.write_text(error_report, encoding="utf-8")
        print(f"  ✗ Timeout for {cluster['cluster']}")
        return False
    except Exception as e:
        error_report = f"# {cluster['cluster']} — שגיאה\n\n{str(e)}\n"
        output_path.write_text(error_report, encoding="utf-8")
        print(f"  ✗ Error: {e}")
        return False


def generate_index(reports_dir, clusters):
    """Generate an index.html for all research reports."""
    html_parts = ["""<!DOCTYPE html>
<html lang="he" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>דוחות מחקר — תוכניות חשודות</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, sans-serif; background: #f8f9fa; color: #333; padding: 20px; direction: rtl; max-width: 900px; margin: 0 auto; }
        h1 { color: #2c3e50; border-bottom: 3px solid #e74c3c; padding-bottom: 10px; }
        .cluster { background: white; border-radius: 8px; padding: 16px 20px; margin: 12px 0; box-shadow: 0 2px 6px rgba(0,0,0,0.08); }
        .cluster h2 { margin: 0 0 8px; font-size: 18px; }
        .cluster .orgs { color: #666; font-size: 13px; margin-bottom: 8px; }
        .cluster .status { font-size: 13px; font-weight: 600; }
        .status.done { color: #27ae60; }
        .status.pending { color: #e67e22; }
        .status.error { color: #e74c3c; }
        a { color: #2980b9; text-decoration: none; }
        a:hover { text-decoration: underline; }
        .meta { font-size: 12px; color: #999; margin-top: 20px; }
    </style>
</head>
<body>
    <h1>דוחות מחקר — ניתוח ארגונים חשודים</h1>
    <p>מחקר מעמיק בסגנון הניתוח של דרור ישראל (9 פרקים, עדשה אידיאולוגית-ערכית)</p>
"""]

    for c in clusters:
        slug = c["cluster"].replace(" ", "_").replace("/", "_")
        report_file = reports_dir / f"{slug}.md"
        status_class = "done" if c.get("status") == "DONE" else ("error" if not report_file.exists() else "done")
        status_text = c.get("status", "PENDING")

        if report_file.exists():
            status_text = "DONE"
            status_class = "done"
            link = f'<a href="{slug}.md">📄 קרא דוח</a>'
        else:
            link = ""

        html_parts.append(f"""
    <div class="cluster">
        <h2>{c["cluster"]}</h2>
        <div class="orgs">ארגונים: {', '.join(c['orgs'])}</div>
        <div class="status {status_class}">{status_text} {link}</div>
    </div>""")

    html_parts.append(f"""
    <div class="meta">נוצר: {datetime.now().strftime('%Y-%m-%d %H:%M')} | עדשה: research_lens.json</div>
</body>
</html>""")

    index_path = reports_dir / "index.html"
    index_path.write_text("\n".join(html_parts), encoding="utf-8")
    print(f"\n✓ Index written to {index_path}")


def main():
    parser = argparse.ArgumentParser(description="Deep research on suspect educational programs")
    parser.add_argument("--cluster", help="Run specific cluster (partial name match)")
    parser.add_argument("--list", action="store_true", help="List all clusters and status")
    parser.add_argument("--index-only", action="store_true", help="Just regenerate index.html")
    args = parser.parse_args()

    lens = load_lens()
    metadata = load_metadata()
    clusters = lens["suspect_clusters"]

    REPORTS_DIR.mkdir(exist_ok=True)

    if args.list:
        print(f"\n{'Cluster':<45} {'Status':<10} {'Orgs'}")
        print("-" * 90)
        for c in clusters:
            slug = c["cluster"].replace(" ", "_").replace("/", "_")
            report_file = REPORTS_DIR / f"{slug}.md"
            status = "DONE" if report_file.exists() else c.get("status", "PENDING")
            print(f"{c['cluster']:<45} {status:<10} {', '.join(c['orgs'][:3])}")
        return

    if args.index_only:
        generate_index(REPORTS_DIR, clusters)
        return

    # Filter clusters
    if args.cluster:
        clusters = [c for c in clusters if args.cluster.lower() in c["cluster"].lower()]
        if not clusters:
            print(f"No cluster matching '{args.cluster}'")
            return

    # Skip already-done clusters
    pending = []
    for c in clusters:
        if c.get("status") == "DONE":
            slug = c["cluster"].replace(" ", "_").replace("/", "_")
            report_file = REPORTS_DIR / f"{slug}.md"
            if report_file.exists():
                print(f"  ⏭ Skipping {c['cluster']} (already done)")
                continue
        pending.append(c)

    if not pending:
        print("All clusters already researched!")
        generate_index(REPORTS_DIR, lens["suspect_clusters"])
        return

    print(f"\n🔍 Researching {len(pending)} clusters...")
    print(f"Analytical lens: 9-chapter framework from Dror Israel report")
    print(f"Output: {REPORTS_DIR}/\n")

    for c in pending:
        programs = get_cluster_programs(c, metadata)
        prompt = build_prompt(c, programs)

        slug = c["cluster"].replace(" ", "_").replace("/", "_")
        output_path = REPORTS_DIR / f"{slug}.md"

        success = run_research(c, prompt, output_path)
        if success:
            c["status"] = "DONE"

    # Update lens file with status
    for lc in lens["suspect_clusters"]:
        for c in pending:
            if lc["cluster"] == c["cluster"] and c.get("status") == "DONE":
                lc["status"] = "DONE"
                slug = c["cluster"].replace(" ", "_").replace("/", "_")
                lc["report_path"] = f"research_reports/{slug}.md"

    with open(LENS_FILE, "w", encoding="utf-8") as f:
        json.dump(lens, f, ensure_ascii=False, indent=2)

    generate_index(REPORTS_DIR, lens["suspect_clusters"])

    done = sum(1 for c in lens["suspect_clusters"] if c.get("status") == "DONE")
    total = len(lens["suspect_clusters"])
    print(f"\n{'='*60}")
    print(f"Research complete: {done}/{total} clusters done")
    print(f"Reports: {REPORTS_DIR}/")
    print(f"{'='*60}")


if __name__ == "__main__":
    main()
