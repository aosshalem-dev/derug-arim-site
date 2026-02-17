#!/usr/bin/env python3
"""
Convert deep research MD files to formatted HTML minisites.
Uses the SEL/Shefi visual style: sticky nav, hero section, source-linked sections.
"""

import re
import json
import sys
from pathlib import Path

TEMPLATE = '''<!DOCTYPE html>
<html lang="he" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{title} — דוח מחקר מעמיק</title>
<style>
* {{ margin: 0; padding: 0; box-sizing: border-box; }}
body {{
  font-family: 'Segoe UI', Tahoma, Arial, sans-serif;
  background: #f5f5f8;
  color: #1a1a2e;
  direction: rtl;
  line-height: 1.8;
  font-size: 17px;
}}
nav {{
  position: sticky; top: 0; z-index: 100;
  background: #1a1a2e; color: #fff;
  padding: 10px 20px;
  display: flex; align-items: center; gap: 14px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.25);
  overflow-x: auto; white-space: nowrap;
}}
nav .brand {{ font-weight: 700; color: #ff8800; flex-shrink: 0; }}
nav a {{ color: #ccc; text-decoration: none; font-size: 0.85em; padding: 8px 4px; border-bottom: 2px solid transparent; flex-shrink: 0; }}
nav a:hover {{ color: #ff8800; border-bottom-color: #ff8800; }}
.hero {{
  background: linear-gradient(135deg, #1a1a2e 0%, #2a5a8a 100%);
  color: #fff; padding: 50px 20px 40px; text-align: center;
}}
.hero h1 {{ font-size: 1.8em; font-weight: 700; margin-bottom: 10px; line-height: 1.4; }}
.hero .subtitle {{ font-size: 1.05em; color: #b0c4de; margin-bottom: 6px; }}
.hero .meta {{ font-size: 0.9em; color: #ff8800; margin-top: 8px; }}
.risk-badge {{
  display: inline-block; padding: 4px 16px; border-radius: 20px;
  font-size: 0.85em; font-weight: 600; margin-top: 10px;
}}
.risk-high {{ background: #e74c3c; color: #fff; }}
.risk-medium {{ background: #f39c12; color: #fff; }}
.risk-low {{ background: #27ae60; color: #fff; }}
.container {{ max-width: 860px; margin: 0 auto; padding: 30px 20px 80px; }}
.toc {{
  background: #fff; border-radius: 8px; padding: 22px 26px;
  margin-bottom: 32px; box-shadow: 0 1px 4px rgba(0,0,0,0.08);
}}
.toc h2 {{ font-size: 1.15em; color: #2a5a8a; margin-bottom: 12px; border-bottom: 2px solid #667eea; padding-bottom: 6px; }}
.toc ol {{ padding-right: 22px; font-size: 0.93em; }}
.toc li {{ margin-bottom: 4px; }}
.toc a {{ color: #2a5a8a; text-decoration: none; }}
.toc a:hover {{ color: #ff8800; text-decoration: underline; }}
.section {{
  background: #fff; border-radius: 8px; padding: 28px 26px;
  margin-bottom: 24px; box-shadow: 0 1px 4px rgba(0,0,0,0.08);
}}
.section h2 {{ font-size: 1.4em; color: #1a1a2e; margin-bottom: 16px; padding-bottom: 6px; border-bottom: 2px solid #667eea; }}
.section h3 {{ font-size: 1.1em; color: #2a5a8a; margin: 18px 0 8px; }}
.section h4 {{ font-size: 1em; color: #444; margin: 14px 0 6px; }}
.section p {{ margin-bottom: 12px; text-align: justify; }}
.section ul, .section ol {{ padding-right: 22px; margin-bottom: 12px; }}
.section li {{ margin-bottom: 5px; }}
blockquote {{
  background: #f0f4fa; border-right: 4px solid #667eea;
  padding: 12px 18px; margin: 12px 0; border-radius: 0 6px 6px 0;
  font-size: 0.94em; color: #333;
}}
.thesis {{
  background: #fff8f0; border-right: 3px solid #ff8800;
  padding: 14px 18px; margin: 12px 0; border-radius: 0 5px 5px 0;
  font-size: 0.96em; color: #6b4500;
}}
table {{
  width: 100%; border-collapse: collapse; margin: 12px 0; font-size: 0.91em;
}}
th {{
  background: #667eea; color: #fff; padding: 8px 12px;
  text-align: right; position: sticky; top: 44px; z-index: 5;
}}
td {{
  padding: 7px 12px; border-bottom: 1px solid #e8e8e8;
  text-align: right; vertical-align: top;
}}
tr:nth-child(even) {{ background: #f8f9fc; }}
a {{ color: #2a5a8a; }}
a:hover {{ color: #ff8800; }}
.back-link {{
  display: inline-block; margin-bottom: 20px; padding: 6px 14px;
  background: #667eea; color: #fff; border-radius: 4px;
  text-decoration: none; font-size: 0.88em;
}}
.back-link:hover {{ background: #5a6fd6; color: #fff; }}
footer {{
  text-align: center; padding: 30px 20px; color: #888; font-size: 0.85em;
}}
strong {{ color: #1a1a2e; }}
code {{
  background: #f0f0f0; padding: 1px 5px; border-radius: 3px;
  font-size: 0.9em;
}}
.source-tag {{
  display: inline-block; background: #e8eef6; padding: 2px 8px;
  border-radius: 10px; font-size: 0.78em; color: #2a5a8a;
  margin: 2px 3px; text-decoration: none;
}}
.source-tag:hover {{ background: #d0dff0; }}
nav .nav-sections {{ display: contents; }}
@media (max-width: 768px) {{
  nav {{
    padding: 8px 12px;
    gap: 8px;
    flex-wrap: wrap;
    white-space: normal;
  }}
  nav .brand {{ font-size: 0.9em; }}
  nav .nav-sections {{ display: none; }}
  .hero {{
    padding: 24px 14px 20px;
  }}
  .hero h1 {{ font-size: 1.25em; line-height: 1.3; }}
  .hero .subtitle {{ font-size: 0.9em; }}
  .container {{ padding: 16px 12px 50px; }}
  .section {{ padding: 18px 14px; }}
  .section h2 {{ font-size: 1.15em; }}
  .toc {{ padding: 14px 16px; }}
  .toc ol {{ font-size: 0.85em; }}
  table {{ font-size: 0.82em; display: block; overflow-x: auto; }}
  th {{ position: static; }}
  blockquote {{ padding: 10px 12px; font-size: 0.88em; }}
  .thesis {{ padding: 10px 12px; font-size: 0.9em; }}
  body {{ font-size: 15px; line-height: 1.65; }}
}}
</style>
</head>
<body>

<nav>
  <span class="brand">דוח מחקר מעמיק</span>
  <a href="../">חזרה לדירוג</a>
  <a href="../research/">כל הדוחות</a>
  <span class="nav-sections">{nav_links}</span>
</nav>

<div class="hero">
  <h1>{title}</h1>
  <div class="subtitle">{subtitle}</div>
  <div class="meta">{meta}</div>
  {risk_badge}
</div>

<div class="container">
  <a href="../research/" class="back-link">← כל דוחות המחקר</a>
  {toc}
  {content}
</div>

<footer>
  פרויקט זהות ציונית — דוח מחקר מעמיק | <a href="../">דירוג ערים</a> | <a href="../research/">כל הדוחות</a>
</footer>

</body>
</html>'''


def extract_title_and_meta(md_text):
    """Extract title, subtitle, and metadata from MD header."""
    lines = md_text.strip().split('\n')
    title = ''
    subtitle = ''
    meta = ''
    risk = ''

    for line in lines[:10]:
        if line.startswith('# ') and not title:
            title = line[2:].strip()
            # Extract org name from title like "Deep Research Report: ACRI (הגוף)"
            if ':' in title:
                title = title.split(':', 1)[1].strip()
        elif line.startswith('## ') and not subtitle:
            subtitle = line[3:].strip()
        elif line.startswith('### '):
            meta = line[4:].strip()

    # Try to detect risk level
    risk_match = re.search(r'(?:risk|סיכון)[:\s]*(high|medium|low|גבוה|בינוני|נמוך)', md_text[:3000], re.IGNORECASE)
    if risk_match:
        r = risk_match.group(1).lower()
        if r in ('high', 'גבוה'):
            risk = 'high'
        elif r in ('medium', 'בינוני'):
            risk = 'medium'
        else:
            risk = 'low'

    return title, subtitle, meta, risk


def md_to_html_sections(md_text):
    """Convert markdown sections to HTML with proper formatting."""
    # Remove the header (first few lines until first ---)
    parts = md_text.split('---', 2)
    if len(parts) >= 3:
        body = parts[2]
    elif len(parts) >= 2:
        body = parts[1]
    else:
        body = md_text

    # Split into sections by ## headers
    sections = re.split(r'\n(?=## )', body)

    html_sections = []
    toc_items = []
    nav_links = []
    section_id = 0

    for section in sections:
        section = section.strip()
        if not section:
            continue

        # Extract section title
        title_match = re.match(r'##\s+(.+)', section)
        if title_match:
            section_title = title_match.group(1).strip()
            section_id += 1
            sid = f'section-{section_id}'
            toc_items.append(f'<li><a href="#{sid}">{section_title}</a></li>')
            nav_links.append(f'<a href="#{sid}">{section_title[:20]}</a>')

            # Convert the section content
            content = section[title_match.end():].strip()
            html_content = convert_md_block(content)

            # Special styling for THESIS section
            extra_class = ''
            if 'THESIS' in section_title.upper() or 'תזה' in section_title:
                html_content = f'<div class="thesis">{html_content}</div>'

            html_sections.append(f'<div class="section" id="{sid}">\n<h2>{section_title}</h2>\n{html_content}\n</div>')
        else:
            # Content without a ## header
            html_content = convert_md_block(section)
            if html_content.strip():
                html_sections.append(f'<div class="section">\n{html_content}\n</div>')

    toc_html = ''
    if toc_items:
        toc_html = f'<div class="toc"><h2>תוכן עניינים</h2><ol>{"".join(toc_items)}</ol></div>'

    return '\n'.join(html_sections), toc_html, ' '.join(nav_links)


def convert_md_block(text):
    """Convert a markdown text block to HTML."""
    lines = text.split('\n')
    html_parts = []
    in_table = False
    in_list = False
    table_rows = []
    list_items = []
    list_type = 'ul'

    def flush_table():
        nonlocal table_rows, in_table
        if table_rows:
            html = '<table>\n'
            for i, row in enumerate(table_rows):
                cells = [c.strip() for c in row.split('|') if c.strip()]
                if i == 0:
                    html += '<tr>' + ''.join(f'<th>{c}</th>' for c in cells) + '</tr>\n'
                elif set(row.replace('|', '').strip()) <= {'-', ' ', ':'}:
                    continue  # separator row
                else:
                    html += '<tr>' + ''.join(f'<td>{inline_md(c)}</td>' for c in cells) + '</tr>\n'
            html += '</table>\n'
            html_parts.append(html)
            table_rows = []
        in_table = False

    def flush_list():
        nonlocal list_items, in_list, list_type
        if list_items:
            tag = list_type
            html = f'<{tag}>\n' + ''.join(f'<li>{inline_md(item)}</li>\n' for item in list_items) + f'</{tag}>\n'
            html_parts.append(html)
            list_items = []
        in_list = False

    for line in lines:
        stripped = line.strip()

        # Table row
        if stripped.startswith('|') and stripped.endswith('|'):
            if in_list:
                flush_list()
            in_table = True
            table_rows.append(stripped)
            continue
        elif in_table:
            flush_table()

        # Headers
        if stripped.startswith('### '):
            if in_list:
                flush_list()
            html_parts.append(f'<h3>{inline_md(stripped[4:])}</h3>\n')
            continue
        if stripped.startswith('#### '):
            if in_list:
                flush_list()
            html_parts.append(f'<h4>{inline_md(stripped[5:])}</h4>\n')
            continue

        # List items
        list_match = re.match(r'^[-*•]\s+(.+)', stripped)
        num_match = re.match(r'^(\d+)\.\s+(.+)', stripped)
        if list_match:
            if in_list and list_type != 'ul':
                flush_list()
            in_list = True
            list_type = 'ul'
            list_items.append(list_match.group(1))
            continue
        elif num_match:
            if in_list and list_type != 'ol':
                flush_list()
            in_list = True
            list_type = 'ol'
            list_items.append(num_match.group(2))
            continue
        elif in_list and stripped and not stripped.startswith('#'):
            # Continuation of list item
            list_items[-1] += ' ' + stripped
            continue
        elif in_list:
            flush_list()

        # Blockquote
        if stripped.startswith('>'):
            html_parts.append(f'<blockquote>{inline_md(stripped[1:].strip())}</blockquote>\n')
            continue

        # Empty line
        if not stripped:
            continue

        # Horizontal rule
        if stripped in ('---', '***', '___'):
            continue

        # Regular paragraph
        html_parts.append(f'<p>{inline_md(stripped)}</p>\n')

    if in_table:
        flush_table()
    if in_list:
        flush_list()

    return ''.join(html_parts)


def inline_md(text):
    """Convert inline markdown (bold, italic, links, code) to HTML."""
    # Bold
    text = re.sub(r'\*\*(.+?)\*\*', r'<strong>\1</strong>', text)
    # Italic
    text = re.sub(r'\*(.+?)\*', r'<em>\1</em>', text)
    # Inline code
    text = re.sub(r'`(.+?)`', r'<code>\1</code>', text)
    # Links
    text = re.sub(r'\[([^\]]+)\]\(([^)]+)\)', r'<a href="\2" target="_blank" rel="noopener">\1</a>', text)
    return text


def convert_file(md_path, output_dir):
    """Convert a single MD file to an HTML minisite page."""
    md_text = Path(md_path).read_text(encoding='utf-8')

    title, subtitle, meta, risk = extract_title_and_meta(md_text)
    content, toc, nav_links = md_to_html_sections(md_text)

    risk_badge = ''
    if risk == 'high':
        risk_badge = '<span class="risk-badge risk-high">סיכון גבוה</span>'
    elif risk == 'medium':
        risk_badge = '<span class="risk-badge risk-medium">סיכון בינוני</span>'
    elif risk == 'low':
        risk_badge = '<span class="risk-badge risk-low">סיכון נמוך</span>'

    html = TEMPLATE.format(
        title=title,
        subtitle=subtitle,
        meta=meta,
        risk_badge=risk_badge,
        nav_links=nav_links,
        toc=toc,
        content=content,
    )

    # Output filename: deep_ACRI.md -> deep_ACRI.html
    out_name = Path(md_path).stem + '.html'
    out_path = Path(output_dir) / out_name
    out_path.write_text(html, encoding='utf-8')
    return out_path


def main():
    research_dir = Path(__file__).parent.parent / 'research'
    output_dir = research_dir  # HTML files alongside MD files

    md_files = sorted(research_dir.glob('deep_*.md'))
    print(f'Found {len(md_files)} research MD files')

    for md_file in md_files:
        try:
            out = convert_file(md_file, output_dir)
            print(f'  ✓ {md_file.name} → {out.name}')
        except Exception as e:
            print(f'  ✗ {md_file.name}: {e}')

    # Also convert cluster reports
    cluster_files = [f for f in research_dir.glob('*.md') if not f.name.startswith('deep_')]
    for md_file in cluster_files:
        try:
            out = convert_file(md_file, output_dir)
            print(f'  ✓ {md_file.name} → {out.name}')
        except Exception as e:
            print(f'  ✗ {md_file.name}: {e}')

    # Update index.html in research/ to link to HTML versions
    index_path = research_dir / 'index.html'
    if index_path.exists():
        index_text = index_path.read_text(encoding='utf-8')
        # Replace .md links with .html links
        updated = re.sub(r'href="(deep_[^"]+)\.md"', r'href="\1.html"', index_text)
        updated = re.sub(r'href="([A-Z][^"]+)\.md"', r'href="\1.html"', updated)
        index_path.write_text(updated, encoding='utf-8')
        print(f'\n  Updated research/index.html links to .html')

    print(f'\nDone! {len(md_files) + len(cluster_files)} files converted.')


if __name__ == '__main__':
    main()
