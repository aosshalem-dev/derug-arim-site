#!/usr/bin/env python3
"""
Build research_data.js from markdown research reports.
Maps org clusters to programs, extracts key findings, generates embeddable JS.
"""

import json
import re
import os
from pathlib import Path

REPORTS_DIR = Path(__file__).parent / "research_reports"
OUTPUT_JS = Path(__file__).parent / "site" / "research_data.js"
DATA_JS = Path("/tmp/derug-arim-deploy/data.js")

# ── Cluster → report file mapping ──
CLUSTERS = {
    "dror_israel": {
        "name_he": "רשת דרור ישראל",
        "name_en": "Dror Israel Network",
        "report_file": "Dror_Israel_network.md",
        "risk_level": "גבוה",
        "risk_score": 5,
        "orgs": ["דרור ישראל", "יסודות לצמיחה דרור", "דרור בתי חינוך", "הנוער העובד והלומד", "המעורר"],
        "summary": "רשת של 14+ קיבוצים עירוניים, שליטה מרוכזת בידי פסח האוספטר. תקציב שנתי 230 מיליון ₪. חינוך ביקורתי, תוכנית 'לגעת בזה' לחינוך מגדרי-מיני. פרשת בני המושבים (פער של 30 מיליון ₪). קשרים עמוקים לזרם הפרוגרסיבי הפוליטי.",
        "key_findings": [
            "רשת עמותות מורכבת עם שליטה מרוכזת",
            "פדגוגיה ביקורתית מובהקת — פאולו פריירה כמודל",
            "תוכנית 'לגעת בזה' — חינוך מגדרי-מיני מגיל צעיר",
            "פרשת בני המושבים — פער כספי של 30 מיליון ₪",
            "מימון ממשלתי מסיבי (230 מיליון ₪/שנה)",
            "חדירה ל-14 קיבוצים עירוניים ברחבי הארץ",
        ],
    },
    "gender_sexuality": {
        "name_he": "ספקי חינוך מיני/מגדרי",
        "name_en": "Gender/Sexuality Education",
        "report_file": "Gender_sexuality_education_providers.md",
        "risk_level": "בינוני-גבוה",
        "risk_score": 4,
        "orgs": ["מידע אמין על מין", "מגע בתיאום", 'חוש"ן', "סדנאות מיניות ומוגנות", "תמורות", "דפנה פייזר", "ליטל סינקלר", "לדעת לבחור נכון", "האגודה לחינוך מיני"],
        "summary": "רשת אקולוגית של ארגוני חינוך מיני הפועלים בתיאום במערכת החינוך. דלת מסתובבת בין משרד החינוך לארגונים (שירי בסין-סביון). קידום תכנים מיניים פרוגרסיביים ללא שקיפות מלאה לגבי הכשרת מנחים ותכנים.",
        "key_findings": [
            "דלת מסתובבת: שירי בסין-סביון — ממשרד החינוך לארגון פרטי",
            "אידיאולוגיה מוצהרת — לא ניטרלי, מקדם שינוי חברתי",
            "חוסר שקיפות לגבי הכשרות ותעודות המנחים",
            "תכנים קיצוניים (קידום פורנוגרפיה ע\"י שלומית הברון)",
            "תקציב חריג: 1,189,257 ₪ בחברתי-ערכי",
            "חדירה רחבה: 15 ערים, 136 בתי ספר",
        ],
    },
    "political_activist": {
        "name_he": "ארגוני אקטיביזם פוליטי בחינוך",
        "name_en": "Political-Activist Orgs",
        "report_file": "Political-activist_orgs_in_education.md",
        "risk_level": "גבוה מאוד",
        "risk_score": 5,
        "orgs": ["האגודה לזכויות האזרח", "להוביל לשינוי", "מחנכים לשינוי", "אפשר אחרת", "מרכז רוסינג"],
        "summary": "ארגונים עם אג'נדה פוליטית מובהקת הפועלים בחינוך. האגודה לזכויות האזרח בדירוג 5/5 — אקטיביזם פרוגרסיבי מפורש. מרכז רוסינג 4/5 — נטייה פוסט-לאומית. מימון זר מסיבי (45+ מיליון אירו ממקורות אירופאיים ואמריקאיים).",
        "key_findings": [
            "האגודה לזכויות האזרח: 5/5 — אג'נדה אקטיביסטית מפורשת",
            "מרכז רוסינג: 4/5 — בין-דתי עם נטייה פוסט-לאומית",
            "80.3% מימון זר במרכז רוסינג — תלות קריטית",
            "45+ מיליון אירו ממקורות זרים (ארה\"ב, EU, נורדיות, גרמניות)",
            "רקע פרוגרסיבי של מנהלים (נעה סטצא)",
            "פדגוגיה פוסט-לאומית תחת כסות 'דיאלוג'",
        ],
    },
    "hartman": {
        "name_he": "רשת מכון הרטמן",
        "name_en": "Hartman Institute Network",
        "report_file": "Hartman_Institute_network.md",
        "risk_level": "בינוני",
        "risk_score": 3,
        "orgs": ["מכון שלום הרטמן", "בשבילנו", "בארי"],
        "summary": "מכון אקדמי-רעיוני עם פדגוגיה פרוגרסיבית-ביקורתית. ריכוז סמכויות בידי הרב דניאל הרטמן (28 שנה). מימון כמעט מוחלט מתורמים זרים (10.5+ מיליון דולר/שנה מצפון אמריקה). פמיניזם ביהדות.",
        "key_findings": [
            "ריכוז סמכויות: הרב דניאל הרטמן — נשיא 28 שנים",
            "פדגוגיה פרוגרסיבית-ביקורתית — לא אנטי-מדינתי אך מאתגר מבנים",
            "מימון זר מסיבי: 10.5+ מיליון דולר/שנה מצפון אמריקה",
            "האקדמיה לנשים — 'בית ספר ניסויי לפמיניזם דתי'",
            "אתגור הרבנות הארתודוקסית והציונות המסורתית דרך פלורליזם",
        ],
    },
    "foreign_coexistence": {
        "name_he": "מימון זר — דו-קיום ופלורליזם",
        "name_en": "Foreign-Funded Coexistence",
        "report_file": "Foreign-funded_coexistence_pluralism.md",
        "risk_level": "גבוה",
        "risk_score": 4,
        "orgs": ["בארי", "פרויקט מרחבים", "מסע לקשר", "ידידות טורונטו"],
        "summary": "ארגונים במימון זר כבד הפועלים בחינוך תחת כסות דו-קיום ופלורליזם. פרויקט מרחבים (ידידות טורונטו) — 800 מיליון ₪ ממימון קנדי. מסע לקשר — סוס טרויאני לאידיאולוגיה פרוגרסיבית בקהילות חרדיות.",
        "key_findings": [
            "פרויקט מרחבים: 800 מיליון ₪ מימון היסטורי מקרן פרידברג (קנדה)",
            "מיקוד בנערות חרדיות — סוס טרויאני לאידיאולוגיה פרוגרסיבית",
            "מקורות מימון אמריקאיים וקנדיים לא מזוהים",
            "קשר לקרן ישראל החדשה דרך שתי\"ל",
            "החדרת תיאוריית מגדר תחת כסות 'יחסים בריאים'",
        ],
    },
    "feminist": {
        "name_he": "ארגוני אג'נדה מגדרית-פמיניסטית",
        "name_en": "Feminist/Gender-Agenda",
        "report_file": "Feminist_gender-agenda_orgs.md",
        "risk_level": "גבוה",
        "risk_score": 4,
        "orgs": ["בועטות", "שער שוויון", "סודקות את תקרת הזכוכית", "המרכז לשוויון מגדרי"],
        "summary": "ארגונים פמיניסטיים הפועלים במערכת החינוך עם אג'נדה מגדרית. 28.7% מימון זר, 44.3% הכנסות לא מזוהות. עלות לבית ספר: 112,430 ₪ — מעיד על פעילות אידיאולוגית אינטנסיבית. מיקוד בנערות בתיכון.",
        "key_findings": [
            "שער שוויון: 28.7% מימון זר (3.9 מיליון ₪)",
            "44.3% מהכנסות לא מזוהות — חוסר שקיפות",
            "עלות לבית ספר: 112,430 ₪ — פעילות אידיאולוגית אינטנסיבית",
            "מימון סביר מקרנות פורד, קרן ישראל החדשה, סורוס",
            "מיקוד בנערות בתיכון — אוכלוסייה פגיעה",
            "רשת פועלת דרך ג'וינט ישראל (השקפת עולם פרוגרסיבית)",
        ],
    },
    "child_protection": {
        "name_he": "הגנת ילד עם שכבה אידיאולוגית",
        "name_en": "Child Protection + Ideology",
        "report_file": "Child_protection_with_ideological_overlay.md",
        "risk_level": "בינוני-גבוה",
        "risk_score": 4,
        "orgs": ["לתת פה", "אל\"י", "מצמיחים", "SafeSchool", "סייף סקול"],
        "summary": "ארגוני הגנת ילד עם שכבה אידיאולוגית. עלייה של 400% בתקציב 'לתת פה' בין 2020-2023. דלת מסתובבת: עמית עדרי (מנכ\"ל לשעבר של משרד החינוך) — יו\"ר דירקטוריון SafeSchool. מימון מ-NIF, שוסטרמן, JFNA.",
        "key_findings": [
            "עלייה של 400% בתקציב 'לתת פה' (2020-2023)",
            "דלת מסתובבת: עמית עדרי (מנכ\"ל חינוך) → SafeSchool",
            "חוסר שקיפות — 'לתת פה' לא מפרסם שמות מייסדים/דירקטורים",
            "שפה פמיניסטית בהודעות הארגון",
            "מימון זר: שוסטרמן, JFNA, קרן ישראל החדשה",
        ],
    },
}

# ── Program ID → cluster mapping (from program_info analysis) ──
PROGRAM_CLUSTER_MAP = {
    # Dror Israel
    "1545": "dror_israel",   # המעורר
    "15286": "dror_israel",  # ספורט בקהילה - החלוץ
    "1592": "dror_israel",   # לגעת בזה
    "10197": "dror_israel",  # דיאלוג חינוכי
    "5915": "dror_israel",   # יסודות

    # Hartman / Bari
    "865": "hartman",        # בארי

    # Political-activist
    "2039": "political_activist",  # להוביל לשינוי

    # Child protection
    "927": "child_protection",   # מצמיחים
    "5974": "child_protection",  # סייף סקול

    # Feminist
    "4664": "feminist",      # בועטות

    # Foreign-funded coexistence
    "1795": "foreign_coexistence",  # אל הנפש - ידידות טורונטו
}

# Also map by program name patterns (for programs in worst_programs that don't have IDs in program_info)
PROGRAM_NAME_PATTERNS = {
    "לגעת בזה": "dror_israel",
    "המעורר": "dror_israel",
    "יסודות": "dror_israel",
    "החלוץ": "dror_israel",
    "דיאלוג חינוכי": "dror_israel",
    "בארי": "hartman",
    "להוביל לשינוי": "political_activist",
    "מצמיחים": "child_protection",
    "סייף סקול": "child_protection",
    "SafeSchool": "child_protection",
    "בועטות": "feminist",
    "מגע בתיאום": "gender_sexuality",
    "חינוך למיניות בריאה": "gender_sexuality",
    "חינוך למיניות נבונה": "gender_sexuality",
    "מין שיח שכזה": "gender_sexuality",
    "התנהגות מינית אחראית": "gender_sexuality",
    "יזמות מגדר ומיניות": "gender_sexuality",
    "הדרכת תכני מגדר": "gender_sexuality",
    "חינוך למיניות בריאה ומודעות מגדרית": "gender_sexuality",
    "ביחד בהסכמה": "gender_sexuality",
    "תודעה- חינוך למיניות": "gender_sexuality",
    "אל הנפש": "foreign_coexistence",
}


def extract_chapter_summaries(md_text):
    """Extract chapter headings and first paragraph as summary from markdown."""
    chapters = []
    # Split by ## headings (chapter level)
    parts = re.split(r'^##\s+', md_text, flags=re.MULTILINE)

    for part in parts[1:]:  # skip preamble
        lines = part.strip().split('\n')
        title = lines[0].strip()

        # Skip if it's a sub-heading (###)
        if title.startswith('#'):
            continue

        # Get first meaningful paragraph (skip empty lines)
        summary_lines = []
        for line in lines[1:]:
            line = line.strip()
            if line.startswith('#'):
                break  # next chapter
            if line and not line.startswith('---'):
                summary_lines.append(line)
                if len(' '.join(summary_lines)) > 300:
                    break

        summary = ' '.join(summary_lines)[:400]
        if summary:
            chapters.append({"title": title, "summary": summary})

    return chapters[:9]  # max 9 chapters


def extract_key_findings(md_text):
    """Extract bullet points from summary/conclusions section."""
    findings = []

    # Look for risk/summary/conclusions section
    patterns = [
        r'(?:סיכום|מסקנות|ממצאים|סיכונים|risk|conclusion)',
    ]

    for pattern in patterns:
        match = re.search(pattern, md_text, re.IGNORECASE)
        if match:
            section = md_text[match.start():match.start()+3000]
            # Extract bullet points
            bullets = re.findall(r'^[-•*]\s+(.+)$', section, re.MULTILINE)
            findings.extend(bullets[:8])
            if findings:
                break

    # Also look for numbered findings
    if not findings:
        bullets = re.findall(r'^\d+\.\s+(.+)$', md_text[-5000:], re.MULTILINE)
        findings.extend(bullets[:8])

    return findings


def extract_risk_level(md_text):
    """Try to extract risk score from the report."""
    # Look for risk patterns
    match = re.search(r'(?:רמת סיכון|risk level|ציון סיכון)[:\s]*(\d)[/\s]*5', md_text, re.IGNORECASE)
    if match:
        return int(match.group(1))

    match = re.search(r'(\d)/5.*(?:סיכון|risk)', md_text, re.IGNORECASE)
    if match:
        return int(match.group(1))

    return None


def process_report(cluster_id, cluster):
    """Process a single research report."""
    report_file = cluster.get("report_file")
    if not report_file:
        # Use embedded data (like Dror Israel)
        return {
            "cluster_id": cluster_id,
            "name_he": cluster["name_he"],
            "name_en": cluster["name_en"],
            "risk_level": cluster.get("risk_level", "—"),
            "risk_score": cluster.get("risk_score", 0),
            "orgs": cluster["orgs"],
            "summary": cluster.get("summary", ""),
            "key_findings": cluster.get("key_findings", []),
            "chapters": [],
            "report_url": None,
            "has_full_report": True,
        }

    filepath = REPORTS_DIR / report_file
    if not filepath.exists():
        print(f"  WARNING: {report_file} not found")
        return None

    md_text = filepath.read_text(encoding='utf-8')

    chapters = extract_chapter_summaries(md_text)
    findings = extract_key_findings(md_text)
    risk = extract_risk_level(md_text) or cluster.get("risk_score", 0)

    # Extract first paragraph as summary
    lines = md_text.split('\n')
    summary_lines = []
    for line in lines:
        if line.strip() and not line.startswith('#') and not line.startswith('---'):
            summary_lines.append(line.strip())
            if len(' '.join(summary_lines)) > 300:
                break
    summary = ' '.join(summary_lines)[:500]

    return {
        "cluster_id": cluster_id,
        "name_he": cluster["name_he"],
        "name_en": cluster["name_en"],
        "risk_level": cluster.get("risk_level", "—"),
        "risk_score": risk,
        "orgs": cluster["orgs"],
        "summary": summary or cluster.get("summary", ""),
        "key_findings": findings or cluster.get("key_findings", []),
        "chapters": chapters,
        "report_url": f"research/{report_file}",
        "has_full_report": True,
    }


def build_program_mapping():
    """Build mapping from program ID and name to cluster."""
    mapping = {}

    # Direct ID mapping
    for pid, cid in PROGRAM_CLUSTER_MAP.items():
        mapping[pid] = cid

    return mapping


def main():
    print("=== Building research_data.js ===")

    # Process all clusters
    clusters_data = {}
    for cluster_id, cluster in CLUSTERS.items():
        print(f"Processing: {cluster['name_en']}...")
        result = process_report(cluster_id, cluster)
        if result:
            clusters_data[cluster_id] = result

    # Build program → cluster mapping
    program_map = build_program_mapping()

    # Build the output
    output = {
        "clusters": clusters_data,
        "program_cluster_map": program_map,
        "program_name_patterns": PROGRAM_NAME_PATTERNS,
    }

    # Write as JS
    js_content = "const RESEARCH = " + json.dumps(output, ensure_ascii=False, indent=2) + ";\n"

    OUTPUT_JS.parent.mkdir(parents=True, exist_ok=True)
    OUTPUT_JS.write_text(js_content, encoding='utf-8')
    print(f"\nWritten: {OUTPUT_JS} ({len(js_content):,} bytes)")
    print(f"Clusters: {len(clusters_data)}")
    print(f"Program mappings: {len(program_map)} direct, {len(PROGRAM_NAME_PATTERNS)} by name")


if __name__ == "__main__":
    main()
