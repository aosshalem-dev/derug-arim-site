# Methodology — דירוג ערים

Last updated: 2026-02-12

---

## 1. ANALYSIS OVERVIEW

The project measures **ideological penetration into Israeli municipal education systems** using three independent data streams combined into a single 0-100 city ranking score.

```
Combined Score = Education Exposure (50%) + Pole B Signals (30%) + Red Flags (20%)
```

Two layers of analysis:
- **Quantitative**: Automated scoring of cities (keyword frequency, budget ratios)
- **Qualitative**: Deep organizational research following 9-chapter framework

---

## 2. QUANTITATIVE ANALYSIS

### 2.1 Combined City Score (0-100)

| Component | Weight | Raw Metric | Normalization |
|-----------|--------|------------|---------------|
| Educational Exposure | 50% | Budget-weighted subversive program ratio | Already 0-100 |
| Pole B (progressive signals) | 30% | Keywords per 10K chars in protocols | `min(raw / 3.0, 1.0) × 100` |
| Red Flags (institutional capture) | 20% | Indicators per 10K chars | `min(raw / 0.15, 1.0) × 100` |

**Color coding**: 50+ Critical (red) | 30-49 High (orange) | 15-29 Medium (yellow) | <15 Low (green)

**Source**: `merge_scores.py:92-106`

### 2.2 Program Risk Rating (1-5 scale)

| Rating | Label | Criteria |
|--------|-------|----------|
| **1** | Minimal Risk | Standard academic/STEM programs. No ideological content detected. |
| **2** | Low Risk | Social-values programs with benign content. General SEL, leadership skills. |
| **3** | Medium Risk | Programs with some ideological markers: rights-based framing, liberal pluralism, foreign funding without clear agenda. |
| **4** | High Risk | Programs with explicit gender/sexuality content, feminist agenda, foreign-funded coexistence pushing specific narrative. Keyword triggers: מגדרי, מיניות, שוויון מגדרי. |
| **5** | Critical Risk | Direct political activism in classrooms, critical pedagogy (Freire), post-national agenda, documented foreign control. Organizations with known anti-state legal history. |

**Scoring method**: Dual-agent consensus. Two AI agents score independently (Agent A, Agent B). Final score = rounded average. Disagreements >1 point flagged for review.

**⚠ GAP**: No formal inter-rater reliability metric. No human validation sample.

### 2.3 Pole A / Pole B Keyword Analysis

**Pole A — Jewish/Zionist Identity** (reference, not scored):
- Keywords: תורה, ציונות, שבת, צה״ל, הקמת המדינה, מורשת, יהדות

**Pole B — Progressive/Critical Pedagogy** (scored):
- Keywords: פדגוגיה ביקורתית, צדק חברתי, דה-קולוניזציה, פוסט-לאומי, מדינת כל אזרחיה, שוויון מגדרי, זהות מגדרית, פירוק מבנים

**Red Flags — Institutional Capture**:
- Indicators: UNESCO partnerships, external consultants, foreign NGO involvement

**Source**: `research_lens.json:67-82`

**⚠ GAP**: Context-blind keyword counting. "צדק חברתי" counted the same whether used approvingly or critically. No NLP/sentiment layer.

### 2.4 Quantitative Products

| Product | Format | Contents | Location |
|---------|--------|----------|----------|
| City ranking table | JSON + HTML | 15 cities, 0-100 combined score | `merged_city_ranking.json`, `site/index.html` |
| Program risk matrix | JS data | 46 programs with 1-5 ratings, org, field, schools, cities | `site/data.js` |
| Exposure scores | JSON | Per-city budget-weighted subversive ratio | `city_exposure_scores.json` |
| Protocol orientation | JSON | Per-city Pole A/B/red flag frequency | `city_orientation_results.json` |

---

## 3. QUALITATIVE ANALYSIS

### 3.1 The 9-Chapter Deep-Dive Framework

Every suspect organizational cluster is analyzed through 9 systematic chapters:

| Ch. | Title | Key Questions | Data Sources |
|-----|-------|---------------|--------------|
| 1 | Organizational Identity & Network | Single entity or NGO network? Power centralization? Rotating leadership? | GuideStar, Companies Registrar |
| 2 | Leadership & Political Connections | Key people, political affiliations, SLAPP lawsuits? | LinkedIn, news, court records |
| 3 | Financial Analysis & Funding | Budget breakdown, foreign donors (NIF, Ford), irregularities? | GuideStar financials, NGO Monitor, IRS 990 |
| 4 | Ideology & Pedagogy | Critical pedagogy? Semantic shifting? "Social justice" centrality? | Curricula, training materials, publications |
| 5 | Problematic Content | Sexual/gender content to minors? Content undermining family/national identity? | Program materials, parent complaints |
| 6 | Scandals & Irregularities | Media exposés? Financial scandals? Silencing critics? | News archives, court records |
| 7 | Policy Influence | No-bid contracts? Alumni in MoE? Curriculum influence at scale? | Government records, procurement data |
| 8 | Values Alignment (8-axis) | Spectrum positioning on 8 ideological axes | See 3.2 below |
| 9 | Risk Map & Conclusions | Overall 1-5 score, key risks, follow-up recommendations | Synthesis of Ch. 1-8 |

**Source**: `research_lens.json`

### 3.2 The 8-Axis Values Spectrum

Each organization is qualitatively positioned on 8 axes:

| # | Axis | Spectrum |
|---|------|----------|
| 1 | Jewish identity & heritage | strengthens ↔ weakens/universalizes |
| 2 | Zionist national identity | affirms ↔ post-national/critical |
| 3 | Family & community structure | supports ↔ deconstructs |
| 4 | IDF & national service | supports ↔ critical/conscientious objection |
| 5 | Democratic values | liberal democracy ↔ radical democracy/activism |
| 6 | Economic orientation | free market ↔ socialist/collectivist |
| 7 | Gender & sexuality | traditional ↔ radical gender theory |
| 8 | Foreign influence | independent ↔ foreign-funded agenda |

**⚠ GAP**: No documented conversion formula from 8-axis qualitative spectrum → single numeric risk score (Ch. 8 → Ch. 9).

### 3.3 Qualitative Products

| Product | Format | Contents | Location |
|---------|--------|----------|----------|
| Cluster research reports | Markdown (7 reports) | 9-chapter analysis per org cluster | `research_reports/*.md` |
| Organization deep-dives | JSON | ACRI, אפשר אחרת — structured data | `acri_research.json`, etc. |
| Suspect cluster map | JSON | 7 clusters with org lists, status | `research_lens.json` |
| Research data for site | JS | Cluster summaries, key findings, program mappings | `site/research_data.js` |

---

## 4. ANALYSIS PIPELINE

```
Layer 1: DATA ACQUISITION
  ├── Municipal PDFs ← scrapers per city (scrape_*.py)
  ├── Gefen data ← API + Excel export
  └── External sources ← WhatsApp, Drive, Looker (manual)

Layer 2: PREPROCESSING
  ├── PDF text extraction (extract_first_pages_template.py)
  ├── Dual-agent sifting (deploy_sifters.py → run_all_sifters.py)
  └── Program metadata enrichment (manual_metadata, gefen_metadata)

Layer 3: ANALYSIS
  ├── Quantitative:
  │   ├── Keyword frequency (city_orientation_scanner.py)
  │   ├── Program scoring (dual-agent, 1-5)
  │   └── Combined ranking (merge_scores.py)
  └── Qualitative:
      ├── 9-chapter deep-dives (research_suspicious.py)
      ├── 8-axis values analysis (values_analysis_agent.py)
      └── Cluster mapping (build_research_data.py)

Layer 4: DELIVERY
  └── Static site (index.html + data.js + research_data.js)
      └── GitHub Pages: aosshalem-dev/derug-arim
```

---

## 5. KNOWN GAPS & TODO

### Methodology Gaps
- [ ] **Weighting justification**: Why 50/30/20? No theoretical or empirical basis documented.
- [ ] **Normalization ceilings**: Pole B cap at 3.0, red flag cap at 0.15 — arbitrary. Should use statistical method (percentile, standard deviation).
- [ ] **Qual→Quant conversion**: No formula for converting 8-axis values analysis → single 1-5 risk score.
- [ ] **Context-blind keywords**: Pole A/B counting ignores context. Need NLP/sentiment layer.
- [ ] **Protocol coverage bias**: Cities have 9%-42% coverage. No adjustment for incomplete data.
- [ ] **Inter-rater reliability**: AI agents score independently but no IRR metric calculated.
- [ ] **Validation**: No ground-truth comparison. No expert panel review. No correlation with external measures.

### Data Gaps
- [ ] **WhatsApp "מיגור מרקסיזם אנטישמי"**: Not yet exported
- [ ] **נווה מכון יכין data**: Google Drive + Looker Studio — blocked by authentication
- [ ] **Temporal freshness**: No documentation of when Gefen data was collected
- [ ] **5 cities education-only**: Have exposure scores but no protocol analysis

### Product Gaps
- [ ] **Program-level qualitative cards**: Only 7 clusters deep-dived. 39 of 46 programs lack individual qualitative analysis.
- [ ] **Trend analysis**: No temporal dimension (year-over-year changes in program deployment).
- [ ] **Network visualization**: Org→program→city connections exist in data but not visualized.
- [ ] **Executive summary**: No single-page findings document for non-technical audience.
