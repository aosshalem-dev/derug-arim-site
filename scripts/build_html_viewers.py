#!/usr/bin/env python3
"""
Build human-readable HTML pages from JSON data files.
Replaces raw JSON links on the site with formatted, browsable pages.
"""

import json
from pathlib import Path

SITE = Path(__file__).parent.parent
TOPIC_LABELS = {
    "lgbt_municipal": "להט\"ב ומוניציפלי",
    "gender_equality": "שוויון מגדרי",
    "sustainability": "קיימות",
    "social_justice": "צדק חברתי",
    "diversity_inclusion": "גיוון והכלה",
    "education_progressive": "חינוך פרוגרסיבי",
}

STYLE = """
* { margin: 0; padding: 0; box-sizing: border-box; }
body {
  font-family: 'Segoe UI', Tahoma, Arial, sans-serif;
  background: #f5f5f8; color: #1a1a2e;
  direction: rtl; line-height: 1.7; font-size: 16px;
}
nav {
  position: sticky; top: 0; z-index: 100;
  background: #1a1a2e; color: #fff;
  padding: 10px 20px;
  display: flex; align-items: center; gap: 14px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.25);
}
nav .brand { font-weight: 700; color: #ff8800; }
nav a { color: #ccc; text-decoration: none; font-size: 0.88em; }
nav a:hover { color: #ff8800; }
.hero {
  background: linear-gradient(135deg, #1a1a2e 0%, #2a5a8a 100%);
  color: #fff; padding: 30px 20px 24px; text-align: center;
}
.hero h1 { font-size: 1.5em; margin-bottom: 6px; }
.hero .sub { font-size: 0.95em; color: #b0c4de; }
.container { max-width: 960px; margin: 0 auto; padding: 24px 16px 60px; }
.card {
  background: #fff; border-radius: 8px; padding: 20px;
  margin-bottom: 16px; box-shadow: 0 1px 4px rgba(0,0,0,0.08);
}
.card h2 { font-size: 1.2em; color: #1a1a2e; margin-bottom: 10px; border-bottom: 2px solid #667eea; padding-bottom: 6px; }
.card h3 { font-size: 1em; color: #2a5a8a; margin: 12px 0 6px; }
table { width: 100%; border-collapse: collapse; font-size: 0.9em; margin: 8px 0; }
th { background: #667eea; color: #fff; padding: 8px 10px; text-align: right; }
td { padding: 6px 10px; border-bottom: 1px solid #eee; text-align: right; vertical-align: top; }
tr:nth-child(even) { background: #f8f9fc; }
a { color: #2a5a8a; }
a:hover { color: #ff8800; }
.badge { display: inline-block; padding: 2px 10px; border-radius: 12px; font-size: 0.8em; font-weight: 600; }
.badge-high { background: #e74c3c; color: #fff; }
.badge-med { background: #f39c12; color: #fff; }
.badge-low { background: #27ae60; color: #fff; }
.topic-tag {
  display: inline-block; background: #e8eef6; padding: 3px 10px;
  border-radius: 12px; font-size: 0.8em; color: #2a5a8a; margin: 2px;
}
.stats-row { display: flex; flex-wrap: wrap; gap: 12px; margin: 12px 0; }
.stat-box { background: #f0f4fa; border-radius: 6px; padding: 10px 16px; text-align: center; flex: 1; min-width: 120px; }
.stat-box .num { font-size: 1.4em; font-weight: 700; color: #2a5a8a; }
.stat-box .label { font-size: 0.78em; color: #666; }
.article-item { padding: 8px 0; border-bottom: 1px solid #f0f0f0; }
.article-item:last-child { border-bottom: none; }
.article-source { font-size: 0.78em; color: #888; }
.article-date { font-size: 0.78em; color: #999; }
.toggle-btn {
  background: #667eea; color: #fff; border: none; padding: 6px 14px;
  border-radius: 4px; cursor: pointer; font-size: 0.85em; margin: 4px 0;
}
.toggle-btn:hover { background: #5a6fd6; }
.hidden { display: none; }
footer { text-align: center; padding: 20px; color: #888; font-size: 0.82em; }
@media (max-width: 768px) {
  nav { padding: 8px 12px; gap: 8px; flex-wrap: wrap; }
  .hero { padding: 18px 12px 14px; }
  .hero h1 { font-size: 1.2em; }
  .container { padding: 12px 8px 40px; }
  .card { padding: 14px 12px; }
  table { display: block; overflow-x: auto; font-size: 0.82em; }
  th { position: static; }
  body { font-size: 14px; }
  .stats-row { gap: 6px; }
  .stat-box { min-width: 90px; padding: 8px 10px; }
  .stat-box .num { font-size: 1.1em; }
}
"""


def build_news_archive():
    """Build data/news/index.html — browsable news archive."""
    with open(SITE / "data/city_news_articles.json", "r", encoding="utf-8") as f:
        data = json.load(f)

    cities = data["cities"]
    total = data["metadata"]["total_articles"]

    # Build city sections
    city_sections = []
    for city_key, city in sorted(cities.items(), key=lambda x: -x[1]["total_articles"]):
        if city["total_articles"] == 0:
            continue

        topics_html = []
        for topic_key, topic_data in city["topics"].items():
            if topic_data["count"] == 0:
                continue

            articles_html = []
            for art in topic_data["articles"][:50]:  # cap at 50 per topic for page size
                title = art.get("title", "ללא כותרת")
                url = art.get("url", "#")
                source = art.get("source", "")
                date = art.get("published", "")
                # Shorten date
                if date and "," in date:
                    parts = date.split(",")
                    date = parts[1].strip()[:12] if len(parts) > 1 else date[:20]
                articles_html.append(
                    f'<div class="article-item">'
                    f'<a href="{url}" target="_blank" rel="noopener">{title}</a>'
                    f' <span class="article-source">{source}</span>'
                    f' <span class="article-date">{date}</span>'
                    f'</div>'
                )

            topic_label = topic_data.get("label", TOPIC_LABELS.get(topic_key, topic_key))
            topic_id = f"{city_key}_{topic_key}"
            remaining = topic_data["count"] - 50
            more_note = f'<div style="color:#888;font-size:0.82em;margin-top:6px">+ {remaining} כתבות נוספות</div>' if remaining > 0 else ''

            topics_html.append(
                f'<h3><span class="topic-tag">{topic_label}</span> ({topic_data["count"]} כתבות)'
                f' <button class="toggle-btn" onclick="toggleSection(\'{topic_id}\')">הצג ▾</button></h3>'
                f'<div id="{topic_id}" class="hidden">'
                + "\n".join(articles_html) + more_note +
                f'</div>'
            )

        city_sections.append(
            f'<div class="card" id="{city_key}">'
            f'<h2>{city["city_name"]} — {city["total_articles"]} כתבות</h2>'
            + "\n".join(topics_html) +
            f'</div>'
        )

    # Build city nav
    city_nav = " | ".join(
        f'<a href="#{ck}">{cv["city_name"]} ({cv["total_articles"]})</a>'
        for ck, cv in sorted(cities.items(), key=lambda x: -x[1]["total_articles"])
        if cv["total_articles"] > 0
    )

    html = f"""<!DOCTYPE html>
<html lang="he" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ארכיון כתבות — דירוג ערים</title>
<style>{STYLE}</style>
</head>
<body>

<nav>
  <span class="brand">ארכיון כתבות</span>
  <a href="../../">חזרה לדירוג</a>
</nav>

<div class="hero">
  <h1>ארכיון כתבות — סיקור חדשותי פרוגרסיבי</h1>
  <div class="sub">{total:,} כתבות מ-{len([c for c in cities.values() if c['total_articles']>0])} ערים | מקור: Google News Hebrew</div>
</div>

<div class="container">
  <div class="card">
    <h2>מתודולוגיה</h2>
    <p>כתבות נאספו מ-Google News RSS בעברית, עם שאילתות מעוגנות בשם העיר + נושאים פרוגרסיביים.
    כל כתבה סווגה לפי נושא: להט"ב ומוניציפלי, שוויון מגדרי, קיימות, צדק חברתי, גיוון והכלה, או חינוך פרוגרסיבי.
    מספר הכתבות משמש כמדד לרמת השיח הפרוגרסיבי בכל עיר — לא כהערכה על איכות או נכונות התוכן.</p>
    <p>מקור: <a href="https://news.google.com/?hl=iw&gl=IL" target="_blank">Google News Hebrew</a> | נאסף: פברואר 2026</p>
  </div>

  <div class="card">
    <h2>ניווט מהיר</h2>
    <div style="font-size:0.88em;line-height:2">{city_nav}</div>
  </div>

  {"".join(city_sections)}
</div>

<footer>פרויקט זהות ציונית — ארכיון כתבות | <a href="../../">דירוג ערים</a></footer>

<script>
function toggleSection(id) {{
  var el = document.getElementById(id);
  if (el) el.classList.toggle('hidden');
}}
</script>
</body>
</html>"""

    out = SITE / "data/news/index.html"
    out.write_text(html, encoding="utf-8")
    print(f"  ✓ {out} ({total:,} articles)")


def build_ranking_page():
    """Build data/ranking.html — human-readable ranking data."""
    with open(SITE / "data/merged_city_ranking.json", "r", encoding="utf-8") as f:
        data = json.load(f)

    rankings = data["rankings"]
    meta = data.get("metadata", {})

    rows = []
    for r in sorted(rankings, key=lambda x: x.get("rank", 99)):
        score = r.get("combined_score", 0)
        if score >= 50:
            badge = '<span class="badge badge-high">קריטי</span>'
        elif score >= 30:
            badge = '<span class="badge badge-med">גבוה</span>'
        else:
            badge = '<span class="badge badge-low">נמוך</span>'

        rows.append(f"""<tr>
  <td>{r.get('rank','')}</td>
  <td><strong>{r.get('city','')}</strong></td>
  <td>{score:.1f} {badge}</td>
  <td>{r.get('exposure_score',0):.1f}</td>
  <td>{r.get('subversive_ratio',0):.1f}%</td>
  <td>{r.get('pole_b',0):.2f}</td>
  <td>{r.get('red_flag',0):.2f}</td>
  <td>{r.get('pole_a',0):.2f}</td>
  <td>{r.get('protocol_coverage','')}</td>
</tr>""")

    html = f"""<!DOCTYPE html>
<html lang="he" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>נתוני דירוג גולמיים — דירוג ערים</title>
<style>{STYLE}</style>
</head>
<body>

<nav>
  <span class="brand">נתוני דירוג</span>
  <a href="../">חזרה לדירוג</a>
</nav>

<div class="hero">
  <h1>נתוני דירוג גולמיים — {len(rankings)} ערים</h1>
  <div class="sub">כל הנתונים שמרכיבים את הציון המשולב</div>
</div>

<div class="container">
  <div class="card">
    <h2>מפתח שדות</h2>
    <table>
      <tr><th>שדה</th><th>הסבר</th><th>משקל בציון</th></tr>
      <tr><td>חשיפה חינוכית</td><td>ציון נורמלי (0-100) המבוסס על תוכניות גפ"ן שסומנו כפרוגרסיביות</td><td>35%</td></tr>
      <tr><td>% תקציב מסומן</td><td>אחוז התקציב החינוכי שמופנה לתוכניות מסומנות</td><td>—</td></tr>
      <tr><td>ציר ב' (פרוגרסיבי)</td><td>ציון מילות מפתח פרוגרסיביות בפרוטוקולים</td><td>15%</td></tr>
      <tr><td>דגלים אדומים</td><td>ציון UNESCO, יועצים חיצוניים ומרכיבים חשודים</td><td>15%</td></tr>
      <tr><td>ציר א' (זהות)</td><td>ציון מילות מפתח זהות יהודית/ציונית — לעיון בלבד</td><td>—</td></tr>
      <tr><td>כיסוי</td><td>מספר פרוטוקולים שנסרקו / סה"כ פרוטוקולים</td><td>—</td></tr>
    </table>
  </div>

  <div class="card">
    <h2>טבלת דירוג</h2>
    <table>
      <tr>
        <th>#</th><th>עיר</th><th>ציון משולב</th><th>חשיפה חינוכית</th>
        <th>% תקציב</th><th>ציר ב'</th><th>דגלים</th><th>ציר א'</th><th>כיסוי</th>
      </tr>
      {"".join(rows)}
    </table>
  </div>

  <div class="card">
    <h2>מקורות</h2>
    <ul>
      <li><a href="https://apps.education.gov.il/gefen" target="_blank">מערכת גפ"ן — משרד החינוך</a></li>
      <li><a href="https://votes25.bechirot.gov.il/" target="_blank">תוצאות בחירות כנסת 25</a></li>
      <li><a href="https://news.google.com/?hl=iw&gl=IL" target="_blank">Google News Hebrew</a></li>
      <li>פרוטוקולי מועצות עיר (15 ערים)</li>
      <li><a href="../research/" target="_blank">דוחות מחקר מעמיק</a></li>
    </ul>
  </div>
</div>

<footer>פרויקט זהות ציונית — נתוני דירוג גולמיים | <a href="../">דירוג ערים</a></footer>

</body>
</html>"""

    out = SITE / "data/ranking.html"
    out.write_text(html, encoding="utf-8")
    print(f"  ✓ {out} ({len(rankings)} cities)")


def build_research_lens():
    """Build data/research_lens.html — research framework viewer."""
    with open(SITE / "data/research_lens.json", "r", encoding="utf-8") as f:
        data = json.load(f)

    chapters_html = []
    for ch in data.get("chapters", []):
        questions = "\n".join(f"<li>{q}</li>" for q in ch.get("questions", []))
        sources = ", ".join(ch.get("data_sources", []))
        chapters_html.append(
            f'<div class="card">'
            f'<h2>{ch["id"]}. {ch["title_he"]}</h2>'
            f'<h3>{ch["title_en"]}</h3>'
            f'<ul>{questions}</ul>'
            f'<p style="font-size:0.82em;color:#666;margin-top:8px"><strong>מקורות נתונים:</strong> {sources}</p>'
            f'</div>'
        )

    clusters_html = []
    for cl in data.get("suspect_clusters", []):
        indicators = "\n".join(f"<li>{i}</li>" for i in cl.get("indicators", []))
        clusters_html.append(
            f'<div class="card">'
            f'<h2>{cl.get("name_he", cl.get("name_en", ""))}</h2>'
            f'<h3>{cl.get("name_en", "")}</h3>'
            f'<ul>{indicators}</ul>'
            f'</div>'
        )

    html = f"""<!DOCTYPE html>
<html lang="he" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>מסגרת המחקר — דירוג ערים</title>
<style>{STYLE}</style>
</head>
<body>
<nav>
  <span class="brand">מסגרת המחקר</span>
  <a href="../">חזרה לדירוג</a>
</nav>
<div class="hero">
  <h1>{data.get('name', 'מסגרת מחקר')}</h1>
  <div class="sub">{data.get('description', '')}</div>
</div>
<div class="container">
  <div class="card"><h2>פרקי ניתוח</h2><p>כל ארגון חשוד עובר ניתוח ב-{len(data.get('chapters',[]))} פרקים:</p></div>
  {"".join(chapters_html)}
  {"<div class='card'><h2>אשכולות חשודים</h2></div>" + "".join(clusters_html) if clusters_html else ""}
</div>
<footer>פרויקט זהות ציונית — מסגרת המחקר | <a href="../">דירוג ערים</a></footer>
</body>
</html>"""

    out = SITE / "data/research_lens.html"
    out.write_text(html, encoding="utf-8")
    print(f"  ✓ {out}")


def build_methodology():
    """Build data/methodology.html — statistical methodology viewer."""
    with open(SITE / "data/methodology_computations.json", "r", encoding="utf-8") as f:
        data = json.load(f)

    irr = data.get("inter_rater_reliability", {})
    norm = data.get("normalization_ceiling_analysis", {})
    cov = data.get("coverage_analysis", {})
    sens = data.get("sensitivity_analysis", {})

    # IRR section
    irr_html = f"""<div class="card">
<h2>אמינות בין-מדרגים (Inter-Rater Reliability)</h2>
<div class="stats-row">
  <div class="stat-box"><div class="num">{irr.get('n_pairs', 0)}</div><div class="label">זוגות שנבדקו</div></div>
  <div class="stat-box"><div class="num">{irr.get('pearson_r', 0):.3f}</div><div class="label">Pearson r</div></div>
  <div class="stat-box"><div class="num">{irr.get('cohens_weighted_kappa_quadratic', 0):.3f}</div><div class="label">Cohen's κ (weighted)</div></div>
  <div class="stat-box"><div class="num">{irr.get('exact_agreement', {}).get('rate', 0):.1%}</div><div class="label">התאמה מדויקת</div></div>
  <div class="stat-box"><div class="num">{irr.get('within_1_point_agreement', {}).get('rate', 0):.1%}</div><div class="label">התאמה ±1</div></div>
</div>
</div>"""

    # Normalization section
    norm_rows = ""
    for key, label in [("pole_b", "ציר ב' (פרוגרסיבי)"), ("red_flag", "דגלים אדומים")]:
        nd = norm.get(key, {})
        norm_rows += f"""<tr>
  <td>{label}</td>
  <td>{nd.get('mean',0):.4f}</td><td>{nd.get('median',0):.4f}</td>
  <td>{nd.get('std_dev',0):.4f}</td><td>{nd.get('min',0):.4f}</td>
  <td>{nd.get('max',0):.4f}</td><td>{nd.get('p95',0):.4f}</td>
  <td>{nd.get('recommended_ceiling_p95x1_5',0):.4f}</td>
</tr>"""

    norm_html = f"""<div class="card">
<h2>ניתוח נורמליזציה</h2>
<table>
<tr><th>מדד</th><th>ממוצע</th><th>חציון</th><th>סטיית תקן</th><th>מינימום</th><th>מקסימום</th><th>P95</th><th>תקרה מומלצת</th></tr>
{norm_rows}
</table>
</div>"""

    # Coverage section
    cov_rows = ""
    for c in cov.get("per_city", []):
        conf_badge = '<span class="badge badge-low">גבוה</span>' if c.get("confidence") == "high" else '<span class="badge badge-med">בינוני</span>' if c.get("confidence") == "medium" else '<span class="badge badge-high">נמוך</span>'
        cov_rows += f"""<tr>
  <td>{c.get('city','')}</td><td>{c.get('files_scanned',0)}</td>
  <td>{c.get('files_total',0)}</td><td>{c.get('coverage_pct',0):.1f}%</td>
  <td>{conf_badge}</td>
</tr>"""

    cov_html = f"""<div class="card">
<h2>ניתוח כיסוי פרוטוקולים</h2>
<table>
<tr><th>עיר</th><th>נסרקו</th><th>סה"כ</th><th>כיסוי</th><th>רמת ביטחון</th></tr>
{cov_rows}
</table>
</div>"""

    html = f"""<!DOCTYPE html>
<html lang="he" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>חישובים סטטיסטיים — דירוג ערים</title>
<style>{STYLE}</style>
</head>
<body>
<nav>
  <span class="brand">חישובים סטטיסטיים</span>
  <a href="../">חזרה לדירוג</a>
</nav>
<div class="hero">
  <h1>חישובים סטטיסטיים ובקרת איכות</h1>
  <div class="sub">אמינות בין-מדרגים, נורמליזציה, כיסוי פרוטוקולים</div>
</div>
<div class="container">
{irr_html}
{norm_html}
{cov_html}
</div>
<footer>פרויקט זהות ציונית — חישובים סטטיסטיים | <a href="../">דירוג ערים</a></footer>
</body>
</html>"""

    out = SITE / "data/methodology.html"
    out.write_text(html, encoding="utf-8")
    print(f"  ✓ {out}")


if __name__ == "__main__":
    print("Building HTML viewers...")
    build_news_archive()
    build_ranking_page()
    build_research_lens()
    build_methodology()
    print("Done!")
